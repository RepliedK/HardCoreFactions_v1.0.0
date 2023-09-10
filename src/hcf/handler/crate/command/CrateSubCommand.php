<?php

declare(strict_types=1);

namespace hcf\handler\crate\command;

use pocketmine\command\CommandSender;

/**
 * Interface CrateSubCommand
 * @package hcf\handler\crate\command
 */
interface CrateSubCommand
{
    
    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function execute(CommandSender $sender, array $args): void;
}