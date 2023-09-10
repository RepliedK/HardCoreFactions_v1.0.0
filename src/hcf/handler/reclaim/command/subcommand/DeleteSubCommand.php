<?php

declare(strict_types=1);

namespace hcf\handler\reclaim\command\subcommand;

use hcf\HCFLoader;
use hcf\handler\reclaim\command\ReclaimSubCommand;
use hcf\player\Player;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

/**
 * Class DeleteSubCommand
 * @package hcf\handler\reclaim\command\subcommand
 */
class DeleteSubCommand implements ReclaimSubCommand
{
    
    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player)
            return;
        
        if (count($args) < 1) {
            $sender->sendMessage(TextFormat::colorize('&cUse /reclaim delete [name]'));
            return;
        }
        $name = $args[0];
        
        if (HCFLoader::getInstance()->getHandlerManager()->getReclaimManager()->getReclaim($name) !== null) {
            $sender->sendMessage(TextFormat::colorize('&cThis reclaim does not exist'));
            return;
        }
        HCFLoader::getInstance()->getHandlerManager()->getReclaimManager()->removeReclaim($name);
        $sender->sendMessage(TextFormat::colorize('&aYou have successfully removed the reclaim ' . $name));
    }
}