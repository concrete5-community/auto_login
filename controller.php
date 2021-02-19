<?php 
namespace Concrete\Package\AutoLogin;

use Concrete\Package\AutoLogin\Src\AutoLogin\AutoLogin;
use Package;
use Page;
use SinglePage;

class Controller extends Package
{
    protected $pkgHandle = 'auto_login';
    protected $appVersionRequired = '5.7.4';
    protected $pkgVersion = '1.0';

    protected $single_pages = array(
        '/dashboard/system/registration/auto_login' => array(
            'cName' => 'Auto Login',
        ),
    );

    public function getPackageName()
    {
        return t("Auto Login");
    }

    public function getPackageDescription()
    {
        return t("Automatically authenticates a user based on its IP address.");
    }

    public function on_start()
    {
        $al = new AutoLogin();
        $al->boot();
    }

    public function install()
    {
        $pkg = parent::install();

        $this->installPages($pkg);
    }

    /**
     * @param Package $pkg
     */
    protected function installPages($pkg)
    {
        foreach ($this->single_pages as $path => $value) {
            if (!is_array($value)) {
                $path = $value;
                $value = array();
            }
            $page = Page::getByPath($path);
            if (!$page || $page->isError()) {
                $single_page = SinglePage::add($path, $pkg);

                if ($value) {
                    $single_page->update($value);
                }
            }
        }
    }
}
