<?php

namespace hcf\module;

use hcf\module\blockshop\BlockShopManager;
use hcf\module\enchantment\EnchantmentManager;
use hcf\module\package\PackageManager;

class ModuleManager {

    public PackageManager $packageManager;
    public BlockShopManager $blockShopManager;
    public EnchantmentManager $enchantmentManager;

    public function __construct(){
        $this->packageManager = new PackageManager;
        $this->blockShopManager = new BlockShopManager;
        $this->enchantmentManager = new EnchantmentManager;
    }

    public function getPackageManager(): PackageManager {
        return $this->packageManager;
    }

    public function getBlockShopManager(): BlockShopManager {
        return $this->blockShopManager;
    }
    
}