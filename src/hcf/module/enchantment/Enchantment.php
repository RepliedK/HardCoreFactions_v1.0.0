<?php

declare(strict_types=1);

namespace hcf\module\enchantment;

use pocketmine\player\Player;

/**
 * Class Enchantment
 * @package hcf\module\enchantment
 */
abstract class Enchantment extends \pocketmine\item\enchantment\Enchantment
{
    
    /**
     * @param Player $player
     */
    public function giveEffect(Player $player): void {}
    
    /**
     * @param Player $player
     */
    public function handleMove(Player $player): void {}
}
