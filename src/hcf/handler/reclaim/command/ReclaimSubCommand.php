<?php

declare(strict_types=1);

namespace hcf\handler\reclaim\command;

use pocketmine\command\CommandSender;

/**
 * Interface ReclaimSubCommand
 * @package hcf\handler\reclaim\command
 */
interface ReclaimSubCommand
{
    
    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function execute(CommandSender $sender, array $args): void;
}