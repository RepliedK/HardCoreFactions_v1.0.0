<?php

declare(strict_types=1);

namespace hcf\koth\command\subcommand;

use hcf\koth\command\KothSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

/**
 * Class StopSubCommand
 * @package hcf\koth\command\subcommand
 */
class StopSubCommand implements KothSubCommand
{
    
    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player)
            return;
        
        if (HCFLoader::getInstance()->getKothManager()->getKothActive() === null) {
            $sender->sendMessage(TextFormat::colorize('&cThere is no koth right now'));
            return;
        }
        HCFLoader::getInstance()->getKothManager()->setKothActive(null);
        $sender->sendMessage(TextFormat::colorize('&cYou have turned off the koth that was on'));
    }
}