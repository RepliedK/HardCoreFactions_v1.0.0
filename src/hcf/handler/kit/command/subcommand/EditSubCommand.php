<?php

declare(strict_types=1);

namespace hcf\handler\kit\command\subcommand;

use hcf\handler\kit\command\KitSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;
use hcf\utils\display\Inventories;

use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;

/**
 * Class EditSubCommand
 * @package hcf\handler\kit\command\subcommand
 */
class EditSubCommand implements KitSubCommand
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
            Inventories::editKitOrganization($sender);
            return;
        }
        
        $kitName = $args[0];
        $kit = HCFLoader::getInstance()->getHandlerManager()->getKitManager()->getKit($kitName);
        if ($kit === null) {
            $sender->sendMessage(TextFormat::colorize('&cThis kit does not exist'));
            return;
        }
        if(isset($args[1])){
            if($args[1] == "item"){
                $kit = HCFLoader::getInstance()->getHandlerManager()->getKitManager()->getKit($kitName);
                if($sender->getInventory()->getItemInHand() instanceof Item) {
                    $sender->sendMessage("ItemInHand not is instace of item");
                    return;
                }
                $kit->setRepresentativeItem($sender->getInventory()->getItemInHand());
                $sender->sendMessage(TextFormat::colorize('&a You have successfully edited the ' . $kit->getName() . ' kit'));
                return;
            }
            if($args[1] == "items"){
                $kit->setItems($sender->getInventory()->getContents());
                $kit->setArmor($sender->getArmorInventory()->getContents());
                $sender->sendMessage(TextFormat::colorize('&a You have successfully edited the ' . $kit->getName() . ' kit'));
                return;
            }
            return;
        }
        $sender->sendMessage(TextFormat::colorize("&cuse /kit edit [name] [item:items]"));
    }
}