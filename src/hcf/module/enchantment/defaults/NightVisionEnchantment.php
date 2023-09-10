<?php

declare(strict_types=1);

namespace hcf\module\enchantment\defaults;

use hcf\module\enchantment\Enchantment;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use pocketmine\player\Player;

/**
 * Class NightVisionEnchantment
 * @package hcf\module\enchantment\defaults
 */
class NightVisionEnchantment extends Enchantment
{
    
    /**
     * NightVisionEnchantment construct.
     */
    public function __construct()
    {
        parent::__construct('NightVision', Rarity::COMMON, ItemFlags::ARMOR, ItemFlags::NONE, 2);
    }
    
    /**
     * @param Player $player
     */
    public function giveEffect(Player $player): void
    {
        $player->getEffects()->add(new EffectInstance(VanillaEffects::NIGHT_VISION(), 10 * 20, 1, false, false));
    }
}