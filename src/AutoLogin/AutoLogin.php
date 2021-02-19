<?php 
namespace Concrete\Package\AutoLogin\Src\AutoLogin;

use Concrete\Authentication\Concrete\Controller;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\User\Event\User as UserEvent;
use Concrete\Core\Utility\IPAddress;
use Config;
use Package;
use Symfony\Component\HttpFoundation\IpUtils;
use User;

class AutoLogin
{
    public function boot()
    {
        if (User::isLoggedIn()) {
            return false;
        }

        $app = Application::getFacadeApplication();

        // Retrieve IP
        $iph = $app->make('helper/validation/ip');
        $ip = $iph->getRequestIP();
        $ip = $ip->getIp(IPAddress::FORMAT_IP_STRING);

        // Couldn't retrieve IP
        if ($ip === null) {
            return false;
        }

        $ip_entry = $this->getIPMatch($ip);

        // No active IP entry found, don't login.
        if (!$ip_entry) {
            return false;
        }

        $u = User::getByUserID($ip_entry['uID']);

        // Invalid or non existing user.
        if (!is_object($u) || !($u instanceof User) || $u->isError()) {
            return false;
        }

        /*
         * We shouldn't auto login if the package "Restrict Login" is installed
         * and the users's IP is not allowed to login. ("Restrict Login" takes precedence)
         */
        $p = Package::getByHandle('restrict_login');

        if ($p && $p->isPackageInstalled() && class_exists("\Concrete\Package\RestrictLogin\Src\RestrictLogin\RestrictLogin")) {
            $rl = new \Concrete\Package\RestrictLogin\Src\RestrictLogin\RestrictLogin();
            if (method_exists($rl, "getAllowedIPs")) {
                $allowed_ips = $rl->getAllowedIPs();

                if (count($allowed_ips) && !$rl->checkIP($ip, $allowed_ips)) {
                    return false;
                }
            }
        }

        $u = User::loginByUserID($ip_entry['uID']);
        
        // Use default C5 authentication.
        $concrete = new Controller();
        $concrete->completeAuthentication($u);
    }

    /**
     * Returns config entry if IP matches.
     *
     * @param string $user_ip
     *
     * @return bool|array
     */
    public function getIPMatch($user_ip)
    {
        $entries = Config::get('auto_login.entries');

        foreach ($entries as $ip => $ip_entry) {
            // Auto login for this IP is currently disabled
            if (!$ip_entry['enabled']) {
                continue;
            }

            $match = IpUtils::checkIp($user_ip, $ip);

            if ($match) {
                return $ip_entry;
            }
        }

        return false;
    }
}
