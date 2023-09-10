<?php

declare(strict_types=1);

namespace hcf\handler\reclaim\command\subcommand;

use hcf\HCFLoader;
use hcf\handler\reclaim\command\ReclaimSubCommand;
use hcf\player\Player;
use hcf\utils\logic\time\Timer;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\utils\TextFormat;

/**
 * Class CreateSubCommand
 * @package hcf\handler\reclaim\command\subcommand
 */
class CreateSubCommand implements ReclaimSubCommand
{
    
    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player)
            return;
        
        if (count($args) < 3) {
            $sender->sendMessage(TextFormat::colorize('&cUse /reclaim create [name] [time] [permission]'));
            return;
        }
        $name = $args[0];
        $time = $args[1];
        $permission = $args[2];
        $contents = $sender->getInventory()->getContents();
        
        if (HCFLoader::getInstance()->getHandlerManager()->getReclaimManager()->getReclaim($name) !== null) {
            $sender->sendMessage(TextFormat::colorize('&cThis reclaim already exists'));
            return;
        }
        
        $time = Timer::time($time);
        
        if (count($contents) === 0) {
            $sender->sendMessage(TextFormat::colorize('&cAssign contents to the reclaim. Put items in your inventory'));
            return;
        }
        HCFLoader::getInstance()->getHandlerManager()->getReclaimManager()->createReclaim($name, $permission, (int) $time, $contents);
        $sender->sendMessage(TextFormat::colorize('&aYou have created the reclaim ' . $name . ' successfully'));
        
        HCFLoader::getInstance()->getHandlerManager()->getReclaimManager()->registerPermission($permission);
    }
}