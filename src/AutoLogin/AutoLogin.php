<?php 
namespace Concrete\Package\AutoLogin\Src\AutoLogin;

use Concrete\Authentication\Concrete\Controller;
use Concrete\Core\Utility\IPAddress;
use Concrete\Package\RestrictLogin\Src\RestrictLogin;
use Config;
use Core;
use Package;
use User;

class AutoLogin
{
    public function boot()
    {
        $entries = Config::get('auto_login.entries');

        if (User::isLoggedIn()) {
            return false;
        }

        // Retrieve IP
        $iph = Core::make('helper/validation/ip');
        $ip = $iph->getRequestIP();
        $ip = $ip->getIp(IPAddress::FORMAT_IP_STRING);

        // Couldn't retrieve IP or IP is not in the config file.
        if ($ip === null or !isset($entries[$ip])) {
            return false;
        }

        $entry = $entries[$ip];

        // Auto login for this IP is currently disabled
        if (!$entry['enabled']) {
            return false;
        }

        $u = User::getByUserID($entry['uID']);

        // Invalid or non existing user.
        if (!is_object($u) || !($u instanceof User) || $u->isError()) {
            return false;
        }

        /*
         * We shouldn't auto login if the package "Restrict Login" is installed
         * and the users's IP is not allowed to login. ("Restrict Login" takes precedence)
         */
        $p = Package::getByHandle('restrict_login');
        
        if ($p && $p->isPackageInstalled() && class_exists("Concrete\Package\RestrictLogin\Src\RestrictLogin\RestrictLogin")) {
            $rl = new RestrictLogin\RestrictLogin();
            if (method_exists($rl, "getAllowedIPs")) {
                $allowed_ips = $rl->getAllowedIPs();

                if (count($allowed_ips) && !in_array($ip, $allowed_ips)) {
                    return false;
                }
            }
        }

        $u = User::loginByUserID($entry['uID']);

        // Use default C5 authentication.
        $concrete = new Controller();
        $concrete->completeAuthentication($u);
    }
}
