<?php

declare(strict_types=1);

namespace hcf\module\enchantment\command;

use pocketmine\command\CommandSender;

/**
 * Interface EnchantmentSubCommand
 * @package hcf\module\enchantment\command
 */
interface EnchantmentSubCommand
{
    
    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function execute(CommandSender $sender, array $args): void;
}