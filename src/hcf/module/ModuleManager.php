<?php

namespace hcf\module;

use hcf\module\enchantment\EnchantmentManager;

class ModuleManager {

    public EnchantmentManager $enchantmentManager;

    public function __construct(){
        $this->enchantmentManager = new EnchantmentManager;
    }
    
}