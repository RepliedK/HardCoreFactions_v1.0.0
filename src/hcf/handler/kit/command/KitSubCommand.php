<?php

declare(strict_types=1);

namespace hcf\handler\kit\command;

use pocketmine\command\CommandSender;

/**
 * Interface KitSubCommand
 * @package hcf\handler\kit\command
 */
interface KitSubCommand
{
    
    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function execute(CommandSender $sender, array $args): void;
}