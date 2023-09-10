<?php

namespace hcf\item;

use hcf\HCFLoader;
use hcf\item\default\PartnerPackage;
use pocketmine\event\Listener;

class ItemManager {

    public function __construct(){
        $this->registerItems(
            new PartnerPackage(),
        );
    }

    public function registerItems(Listener ...$deafult): void {
        foreach($deafult as $abilitie)
            HCFLoader::getInstance()->getServer()->getPluginManager()->registerEvents($abilitie, HCFLoader::getInstance());
    }
    
}