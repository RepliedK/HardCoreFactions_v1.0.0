<?php

declare(strict_types=1);

namespace hcf\handler\kit\command\subcommand;

use hcf\handler\kit\command\KitSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

/**
 * Class DeleteSubCommand
 * @package hcf\handler\kit\command\subcommand
 */
class DeleteSubCommand implements KitSubCommand
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
            $sender->sendMessage(TextFormat::colorize('&c/kit delete [string: kitName]'));
            return;
        }
        $kitName = $args[0];
        
        if (HCFLoader::getInstance()->getHandlerManager()->getKitManager()->getKit($kitName) === null) {
            $sender->sendMessage(TextFormat::colorize('&cThis kit does not exist'));
            return;
        }
        HCFLoader::getInstance()->getHandlerManager()->getKitManager()->removeKit($kitName);
        $sender->sendMessage(TextFormat::colorize('&cYou have successfully removed kit ' . $kitName));
    }
}