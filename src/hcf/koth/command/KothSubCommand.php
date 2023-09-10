<?php

declare(strict_types=1);

namespace hcf\koth\command;

use pocketmine\command\CommandSender;

/**
 * Interface KothSubCommand
 * @package hcf\koth\command
 */
interface KothSubCommand
{
    
    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function execute(CommandSender $sender, array $args): void;
}