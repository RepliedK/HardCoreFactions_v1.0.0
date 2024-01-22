<?php

declare(strict_types=1);

namespace hcf\database;

use hcf\HCFLoader;
use hcf\utils\logic\serialize\Serialize;
use hcf\module\package\PackageManager;
use pocketmine\utils\Config;

class DataProvider {

    public Config $kothConfig, $claimConfig, $kitConfig, $reclaimConfig, $crateConfig;

    public function __construct(){
        $plugin = HCFLoader::getInstance();
        $directory = $plugin->getDataFolder() . 'database' . DIRECTORY_SEPARATOR;

        # Creation of folders that do not exist
        if (!is_dir($directory))
            @mkdir($directory);

        if (!is_dir($directory.'players'))
            @mkdir($directory.'players');

        if (!is_dir($directory.'factions'))
            @mkdir($directory.'factions');

        # Save default config
        $plugin->saveDefaultConfig();

        # Creation configs and save variables
        $this->crateConfig = new Config($directory.'crates.yml', Config::YAML);
        $this->kothConfig = new Config($directory.'koths.yml', Config::YAML);
        $this->claimConfig = new Config($directory.'claims.yml', Config::YAML);
        $this->reclaimConfig = new Config($directory.'reclaims.yml', Config::YAML);
        $this->kitConfig = new Config($directory.'kits.yml', Config::YAML, ["organization" => [], "kits" => []]);
    }

    public function save(): void {
        $this->savePlayers();
        $this->saveFactions();
        $this->saveKoths();
        $this->saveClaims();
        $this->saveKits();
        $this->saveReclaims();
        $this->saveCrates();
    }

    public function getReclaimConfig(): Config {
        return $this->reclaimConfig;
    }

    public function getKitConfig(): Config {
        return $this->kitConfig;
    }

    public function getKothConfig(): Config {
        return $this->kothConfig;
    }

    public function getClaimsConfig(): Config {
        return $this->claimConfig;
    }

    public function getCrateConfig(): Config {
        return $this->crateConfig;
    }


    public function getReclaims(): array {
        $reclaims = [];
        foreach ($this->reclaimConfig->getAll() as $name => $data) {
            $reclaim = [
                'permission' => $data['permission'],
                'time' => (int) $data['time'],
                'contents' => []
            ];
            if (isset($data['contents'])) {
                foreach ($data['contents'] as $item)
                    $reclaim['contents'][] = Serialize::deserialize($item);
            }
            $reclaims[$name] = $reclaim;
        }
        return $reclaims;
    }

    public function getPlayers(): array {
        $players = [];
        foreach (glob(HCFLoader::getInstance()->getDataFolder() . 'database/players/'.'*.yml') as $file)
            $players[basename($file, '.yml')] = (new Config(HCFLoader::getInstance()->getDataFolder() . 'database/'.'players/'.basename($file), Config::YAML))->getAll();
        return $players;
    }

    public function getFactions(): array {
        $factions = [];

        foreach (glob(HCFLoader::getInstance()->getDataFolder() . 'database/factions/'.'*.yml') as $file)
            $factions[basename($file, '.yml')] = (new Config(HCFLoader::getInstance()->getDataFolder() . 'database/'.'factions/'.basename($file), Config::YAML))->getAll();
        return $factions;
    }

    public function getKoths(): array {
        $koths = [];
        foreach ($this->kothConfig->getAll() as $name => $data) {
            $koths[$name] = $data;
        }
        return $koths;
    }

    public function getClaims(): array {
        $claims = [];
        foreach ($this->claimConfig->getAll() as $name => $data) {
            $claims[$name] = $data;
        }
        return $claims;
    }

    public function getKits(): array {
        $kits = [];
        foreach ($this->kitConfig->get('kits') as $name => $data) {
            if ($data['representativeItem'] !== null)
                $data['representativeItem'] = Serialize::deserialize($data['representativeItem']);
            if (isset($data['items'])) {
                foreach ($data['items'] as $slot => $item){
                    $data['items'][$slot] = Serialize::deserialize($item);
                }
            }
            if (isset($data['armor'])) {
                foreach ($data['armor'] as $slot => $armor){
                    $data['armor'][$slot] = Serialize::deserialize($armor);
                }
            }
            $kits[$name] = $data;
        }
        return $kits;
    }

    public function getCrates(): array {
        $crates = [];
        foreach ($this->crateConfig->getAll() as $name => $data) {
            if (isset($data['items'])) {
                foreach ($data['items'] as $slot => $item)
                    $data['items'][$slot] = Serialize::deserialize($item);
            }
            $data['key'] = Serialize::deserialize($data['key']);
            $crates[$name] = $data;
        }
        return $crates;
    }

    public function saveCrates(): void {
        $crates = [];
        foreach (HCFLoader::getInstance()->getHandlerManager()->getCrateManager()->getCrates() as $crate) {
            $crates[$crate->getName()] = $crate->getData();
        }
        $this->crateConfig->setAll($crates);
        $this->crateConfig->save();
    }

    public function saveReclaims(): void {
        $reclaims = [];

        foreach (HCFLoader::getInstance()->getHandlerManager()->getReclaimManager()->getReclaims() as $reclaim) {
            $reclaims[$reclaim->getName()] = $reclaim->getData();
        }
        $this->reclaimConfig->setAll($reclaims);
        $this->reclaimConfig->save();
    }

    public function savePlayers(): void {
        foreach (HCFLoader::getInstance()->getSessionManager()->getSessions() as $xuid => $session) {
            $config = new Config(HCFLoader::getInstance()->getDataFolder() . 'database/players' . DIRECTORY_SEPARATOR  . $xuid . '.yml', Config::YAML);
            $config->setAll($session->getData());
            $config->save();
        }
    }

    public function saveFactions(): void {
        foreach (HCFLoader::getInstance()->getFactionManager()->getFactions() as $name => $faction) {
            $config = new Config(HCFLoader::getInstance()->getDataFolder() . 'database/factions/'.$name . '.yml', Config::YAML);
            $config->setAll($faction->getData());
            $config->save();
        }
    }

    public function saveKoths(): void {
        $koths = [];

        foreach (HCFLoader::getInstance()->getKothManager()->getKoths() as $koth) {
            $koths[$koth->getName()] = $koth->getData();
        }
        $this->kothConfig->setAll($koths);
        $this->kothConfig->save();
    }

    public function saveKits(): void {
        $kits = [];

        foreach (HCFLoader::getInstance()->getHandlerManager()->getKitManager()->getKits() as $kit) {
            $kits[$kit->getName()] = $kit->getData();
        }
        $this->kitConfig->set('kits', $kits);
        $this->kitConfig->save();
    }

    public function saveClaims(): void {
        $claims = [];

        foreach (HCFLoader::getInstance()->getClaimManager()->getClaims() as $name => $claim) {
            $claims[$name] = $claim->getData();
        }
        $this->claimConfig->setAll($claims);
        $this->claimConfig->save();
    }

}