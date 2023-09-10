<?php

namespace hcf\module\package;

use hcf\HCFLoader;
use hcf\module\package\PartnerPackage;
use hcf\module\package\commands\PartnerPackagesCommand;

class PackageManager {

    public static $instance;

    public function __construct(){
        HCFLoader::getInstance()->getServer()->getCommandMap()->register("/pkg", new PartnerPackagesCommand());
        self::$instance = new PartnerPackage(HCFLoader::getInstance()->getProvider()->getPackage());
    }

    public static function getPartnerPackage(): PartnerPackage {
        return self::$instance;
    }

}

