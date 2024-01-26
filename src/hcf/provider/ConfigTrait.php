<?php

namespace hcf\provider;

use hcf\HCFLoader;
use pocketmine\utils\Config;

trait ConfigTrait {

    public Config $kothConfig, $claimConfig, $kitConfig, $reclaimConfig, $crateConfig;

    public function setup_configs(): void {
        $directory = HCFLoader::getInstance()->getDataFolder() . 'database' . DIRECTORY_SEPARATOR;
        $this->crateConfig = new Config($directory.'crates.yml', Config::YAML);
        $this->kothConfig = new Config($directory.'koths.yml', Config::YAML);
        $this->claimConfig = new Config($directory.'claims.yml', Config::YAML);
        $this->reclaimConfig = new Config($directory.'reclaims.yml', Config::YAML);
        $this->kitConfig = new Config($directory.'kits.yml', Config::YAML, ["organization" => [], "kits" => []]);
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

}