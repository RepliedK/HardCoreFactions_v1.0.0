<?php

declare(strict_types=1);

namespace hcf\handler\crate\command\subcommand;

use hcf\handler\crate\command\CrateSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

/**
 * Class DeleteSubCommand
 * @package hcf\handler\crate\command\subcommand
 */
class DeleteSubCommand implements CrateSubCommand
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
            $sender->sendMessage(TextFormat::colorize('&c/crate delete [string: crateName]'));
            return;
        }
        $crateName = $args[0];
        
        if (HCFLoader::getInstance()->getHandlerManager()->getCrateManager()->getCrate($crateName) === null) {
            $sender->sendMessage(TextFormat::colorize('&cThis crate does not exist'));
            return;
        }
        HCFLoader::getInstance()->getHandlerManager()->getCrateManager()->removeCrate($crateName);
        $sender->sendMessage(TextFormat::colorize('&cYou have removed the crate ' . $crateName . '. Now remove the chests that have been created with this crate'));
    }
}