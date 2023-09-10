<?php

declare(strict_types=1);

namespace hcf\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class ListCommand extends Command
{
    
    /**
     * ListCommand construct.
     */
    public function __construct()
    {
        parent::__construct('players', 'Use command for list players');
        $this->setPermission("use.player.command");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $sender->sendMessage(TextFormat::colorize('&ePlayers playing: &f' . count($sender->getServer()->getOnlinePlayers()) . "\n"));
    }
}