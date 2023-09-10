<?php

declare(strict_types=1);

namespace hcf\handler\reclaim\command\subcommand;

use hcf\HCFLoader;
use hcf\handler\reclaim\command\ReclaimSubCommand;
use hcf\player\Player;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

/**
 * Class EditSubCommand
 * @package hcf\handler\reclaim\command\subcommand
 */
class EditSubCommand implements ReclaimSubCommand
{
    
    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player)
            return;
        
        if (!isset($args[0])) {
            $sender->sendMessage(TextFormat::colorize('&cUse /reclaim edit [name]'));
            return;
        }
        $name = $args[0];
        
        if (HCFLoader::getInstance()->getHandlerManager()->getReclaimManager()->getReclaim($name) === null) {
            $sender->sendMessage(TextFormat::colorize('&cThis reclaim does not exist'));
            return;
        }
        
        if (count($sender->getInventory()->getContents()) === 0) {
            $sender->sendMessage(TextFormat::colorize('&cAssign contents to the reclaim. Put items in your inventory'));
            return;
        }
        HCFLoader::getInstance()->getHandlerManager()->getReclaimManager()->getReclaim($name)->setContents($sender->getInventory()->getContents());
        $sender->sendMessage(TextFormat::colorize('&aYou have edited the content of the reclaim ' . $name));
    }
}