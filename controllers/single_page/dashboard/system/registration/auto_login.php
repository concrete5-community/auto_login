<?php  
namespace Concrete\Package\AutoLogin\Controller\SinglePage\Dashboard\System\Registration;

use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Utility\IPAddress;
use Config;
use Core;
use Exception;
use Request;
use Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use User;
use View;

class AutoLogin extends DashboardPageController
{
    public function view()
    {
        $entries = Config::get('auto_login.entries', array());

        foreach ($entries as $ip => $entry) {
            $u = User::getByUserID($entry['uID']);
            if (!$u) {
                unset($entries[$ip]);
            } else {
                $entries[$ip]['username'] = $u->getUserName();
            }
        }


        $this->set('entries', $entries);
    }

    /**
     * Render dialog add_ip.
     */
    public function modify_dialog()
    {
        $iph = $this->app->make('helper/validation/ip');
        $ip = $iph->getRequestIP();

        $entry = array(
            'old_ip' => null,
            'ip' => $ip->getIP(IPAddress::FORMAT_IP_STRING),
            'uID' => null,
            'description' => null,
            'enabled' => true
        );

        $view = new View('modify_ip_dialog');
        $view->addScopeItems(array(
            'token' => $this->token
        ));
        $view->setPackageHandle('auto_login');

        if (isset($_POST['ip'])) {
            $ip = $_POST['ip'];
            $entries = Config::get('auto_login.entries', array());
            if (isset($entries[$ip])) {
                $entry = $entries[$ip];
                $entry['old_ip'] = $ip;
                $entry['ip'] = $ip;
            }
        }

        $view->addScopeItems(array('entry' => $entry));

        $response = new Response($view->render());
        $response->send();

        Core::shutdown();
    }

    /**
     * Handle POST data from modify_ip dialog.
     */
    public function modify()
    {
        $json = array('error' => null, 'message' => null);
        $req = Request::getInstance();
        $entries = Config::get('auto_login.entries');
        $sec = Core::make('helper/security');

        try {
            $token = trim($req->get('token'));
            if (!$this->token->validate('auto_login::ip.modify', $token)) {
                throw new Exception(t('Invalid token'));
            }

            $ip = trim($req->get('ip'));

            if (
                (!$req->post('old_ip') && isset($entries[$ip])) ||
                ($req->post('old_ip') != $ip && isset($entries[$ip]))
            ) {
                throw new Exception(t('This IP address already exists'));
            }

            try {
                $ip = new IPAddress($ip);
            } catch (Exception $e) {
                throw new Exception(t('Invalid IP address'));
            }

            if ($ip instanceof IPAddress && $ip->getIp() === null) {
                throw new Exception(t('Invalid IP address'));
            }

            $ip = $ip->getIp(IPAddress::FORMAT_IP_STRING);

            $user_id = trim($req->get('userID'));
            $user = User::getByUserID($user_id);

            if (!$user) {
                throw new Exception(t('Invalid user'));
            }

            $description = $sec->sanitizeString($req->get('description'));
            $enabled = (boolean) $req->get('enabled');

            $entries[$ip] = array(
                'uID' => $user_id,
                'description' => $description,
                'enabled' => $enabled,
            );

            // If IP has changed in modify dialog, delete the old entry.
            $old_ip = $req->get('old_ip');
            if ($old_ip && $old_ip !== $ip) {
                if (isset($entries[$old_ip])) {
                    unset($entries[$old_ip]);
                }
            }

            Config::save('auto_login.entries', $entries);
        } catch (Exception $e) {
            $json['error'] = true;
            $json['message'] = $e->getMessage();
        }

        $response = new JsonResponse($json);
        $response->send();

        Core::shutdown();
    }

    public function add_success()
    {
        $this->view();
        $this->set('message', t('IP address successfully added.'));
    }

    public function update_success()
    {
        $this->view();
        $this->set('message', t('IP address successfully updated.'));
    }

    /**
     * Delete IP entry.
     */
    public function delete()
    {
        $json = array('error' => null, 'message' => null);
        $req = Request::getInstance();

        $token = trim($req->get('token'));
        $ip = trim($req->get('ip'));
        if ($this->token->validate("auto_login::modify.{$ip}", $token)) {
            if ($ip) {
                $entries = Config::get('auto_login.entries');

                if (isset($entries[$ip])) {
                    unset($entries[$ip]);

                    Config::save('auto_login.entries', $entries);
                }
            } else {
                $json['error'] = true;
                $json['message'] = t('Invalid request');
            }
        } else {
            $json['error'] = true;
            $json['message'] = t('Invalid token');
        }

        $response = new JsonResponse($json);
        $response->send();

        Core::shutdown();
    }

    public function delete_success()
    {
        $this->view();
        $this->set('message', t('IP address successfully deleted.'));
    }
}
