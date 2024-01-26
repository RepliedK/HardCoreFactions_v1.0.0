<?php

declare(strict_types=1);

namespace hcf\database;

use hcf\HCFLoader;
use hcf\provider\DataProvider;
use pocketmine\utils\Config;

class DataSaver {

    public static function saveAll(DataProvider $dataProvider): void {
        self::saveCrates(HCFLoader::getInstance()->getHandlerManager()->getCrateManager()->getCrates());
        self::saveReclaims(HCFLoader::getInstance()->getHandlerManager()->getReclaimManager()->getReclaims());
        self::savePlayers(HCFLoader::getInstance()->getSessionManager()->getSessions());
        self::saveFactions(HCFLoader::getInstance()->getFactionManager()->getFactions());
        self::saveKoths(HCFLoader::getInstance()->getKothManager()->getKoths());
        self::saveKits(HCFLoader::getInstance()->getHandlerManager()->getKitManager()->getKits());
        self::saveClaims(HCFLoader::getInstance()->getClaimManager()->getClaims());
    }

    public static function saveCrates(array $cratesData): void {
        $crateConfig = new Config(HCFLoader::getInstance()->getDataFolder() . 'database/crates.yml', Config::YAML);
        $crateConfig->setAll($cratesData);
        $crateConfig->save();
    }

    public static function saveReclaims(array $reclaimsData): void {
        $reclaimConfig = new Config(HCFLoader::getInstance()->getDataFolder() . 'database/reclaims.yml', Config::YAML);
        $reclaimConfig->setAll($reclaimsData);
        $reclaimConfig->save();
    }

    public static function savePlayers(array $playersData): void {
        foreach ($playersData as $xuid => $sessionData) {
            $config = new Config(HCFLoader::getInstance()->getDataFolder() . 'database/players/' . $xuid . '.yml', Config::YAML);
            $config->setAll($sessionData);
            $config->save();
        }
    }

    public static function saveFactions(array $factionsData): void {
        foreach ($factionsData as $name => $factionData) {
            $config = new Config(HCFLoader::getInstance()->getDataFolder() . 'database/factions/' . $name . '.yml', Config::YAML);
            $config->setAll($factionData);
            $config->save();
        }
    }

    public static function saveKoths(array $kothsData): void {
        $kothConfig = new Config(HCFLoader::getInstance()->getDataFolder() . 'database/koths.yml', Config::YAML);
        $kothConfig->setAll($kothsData);
        $kothConfig->save();
    }

    public static function saveKits(array $kitsData): void {
        $kitConfig = new Config(HCFLoader::getInstance()->getDataFolder() . 'database/kits.yml', Config::YAML);
        $kitConfig->set('kits', $kitsData);
        $kitConfig->save();
    }

    public static function saveClaims(array $claimsData): void {
        $claimConfig = new Config(HCFLoader::getInstance()->getDataFolder() . 'database/claims.yml', Config::YAML);
        $claimConfig->setAll($claimsData);
        $claimConfig->save();
    }

}