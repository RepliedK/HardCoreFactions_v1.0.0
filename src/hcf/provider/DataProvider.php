<?php

declare(strict_types=1);

namespace hcf\provider;

use hcf\database\DataSaver;
use hcf\HCFLoader;
use hcf\utils\logic\serialize\Serialize;
use hcf\module\package\PackageManager;
use pocketmine\utils\Config;

class DataProvider {

    use ConfigTrait;

    public function __construct(){
        $plugin = HCFLoader::getInstance();
        $plugin->saveDefaultConfig();

        $directorys = [$plugin->getDataFolder() . 'database' . DIRECTORY_SEPARATOR, $plugin->getDataFolder() . 'database' . DIRECTORY_SEPARATOR . "players", $plugin->getDataFolder() . 'database' . DIRECTORY_SEPARATOR . "factions"];
        foreach($directorys as $directory){
            if(!is_dir($directory)) @mkdir($directory);
        }

        $this->setup_configs();
    }

    public function save(): void {
        DataSaver::saveAll($this);
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

}