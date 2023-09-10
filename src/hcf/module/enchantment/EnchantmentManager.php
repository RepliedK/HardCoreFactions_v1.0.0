<?php

declare(strict_types=1);

namespace hcf\module\enchantment;

use hcf\module\enchantment\command\EnchantmentCommand;
use hcf\module\enchantment\defaults\FireResistanceEnchantment;
use hcf\module\enchantment\defaults\ImplantsEnchantment;
use hcf\module\enchantment\defaults\InvisibilityEnchantment;
use hcf\module\enchantment\defaults\NightVisionEnchantment;
use hcf\module\enchantment\defaults\SpeedEnchantment;
use hcf\HCFLoader;

use pocketmine\data\bedrock\EnchantmentIdMap;

/**
 * Class EnchantmentManager
 * @package hcf\module\enchantment
 */
class EnchantmentManager
{
    
    /** @var Enchantment[] */
    private array $enchantments = [];
    
    /**
     * EnchantmentManager construct.
     */
    public function __construct()
    {
        # Register custom enchants
        EnchantmentIdMap::getInstance()->register(EnchantmentIds::SPEED, $this->enchantments[40] = new SpeedEnchantment());
        EnchantmentIdMap::getInstance()->register(EnchantmentIds::INVISIBILITY, $this->enchantments[41] = new InvisibilityEnchantment());
        EnchantmentIdMap::getInstance()->register(EnchantmentIds::NIGHT_VISION, $this->enchantments[42] = new NightVisionEnchantment());
        EnchantmentIdMap::getInstance()->register(EnchantmentIds::FIRE_RESISTANCE, $this->enchantments[43] = new FireResistanceEnchantment());
        EnchantmentIdMap::getInstance()->register(EnchantmentIds::IMPLANTS, $this->enchantments[44] = new ImplantsEnchantment());
        
        # Register command
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new EnchantmentCommand());
    }
    
    /**
     * @return Enchantment[]
     */
    public function getEnchantments(): array
    {
        return $this->enchantments;
    }
}