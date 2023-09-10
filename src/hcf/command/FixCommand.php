<?php

declare(strict_types=1);

namespace hcf\command;

use hcf\player\Player;
use hcf\utils\logic\serialize\Serialize;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\item\Armor;
use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;

/**
 * Class FixCommand
 * @package hcf\command
 */
class FixCommand extends Command
{
	
    /**
     * FixCommand construct.
     */
    public function __construct()
    {
        parent::__construct('fix', 'Command for /fix');
        $this->setPermission('fix.command');
    }
	
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$sender instanceof Player)
            return;
            
        if (!$this->testPermission($sender))
            return;
    	
        if (count($args) < 1) {
            $sender->sendMessage(
                TextFormat::colorize('&eFix commands') . "\n" .
                TextFormat::colorize('&7/fix hand - &eFix the item you have in your hand') . "\n" .
                TextFormat::colorize('&7/fix all - &eFix all the items in your inventory and your armor') . "\n" .
                TextFormat::colorize('&7/fix all [player] - &eFixes all items in a player\'s inventory and armor')
            );
            return;
        }
        
        switch (strtolower($args[0])) {
            case 'hand':
                $item = $sender->getInventory()->getItemInHand();
    
                if (!$item instanceof Durable) {
                    $sender->sendMessage(TextFormat::colorize('&cYou have no fixable items in hand'));
                    return;
                }
    
                if ($item->getDamage() > 0) {
                    $sender->sendMessage(TextFormat::colorize('&cThis item is already fixed'));
                    return;
                }
                $newItem = $item->setDamage(0);     
                $sender->getInventory()->setItemInHand($newItem);
                $sender->sendMessage(TextFormat::colorize('&aYou have successfully fixed the item in your hand'));
                break;
    
            case 'all':
                if (count($args) < 2) {
                    foreach ($sender->getInventory()->getContents() as $slot => $item) {
                        if ($item instanceof Durable && $item->getDamage() > 0) {
                            $newItem = $item->setDamage(0);   
                            $sender->getInventory()->setItem($slot, $newItem);
                        }
                    }
    				
                    foreach ($sender->getArmorInventory()->getContents() as $slot => $armor) {
                        if(!$armor instanceof Armor) return;
                        if ($armor->getDamage() > 0) {
                            $newArmor = $armor->setDamage(0);
                            $sender->getArmorInventory()->setItem($slot, $newArmor);
                        }
                    }
                    $sender->sendMessage(TextFormat::colorize('&aYou have fixed all the items in your inventory and armor'));
                    return;
                }
    
                if (!$sender->hasPermission('fix.player.command')) {
                    $sender->sendMessage(TextFormat::colorize('&cYou do not have permission to use this command'));
                    return;
                }
                $player = $sender->getServer()->getPlayerByPrefix($args[1]);
    			
                if (!$player instanceof Player) {
                    $sender->sendMessage(TextFormat::colorize('&cThe player is not online'));
                    return;
                }
    
                foreach ($player->getInventory()->getContents() as $slot => $item) {
                    if ($item instanceof Durable && $item->getDamage() > 0) {
                        $newItem = $item->setDamage(0);     
                        $player->getInventory()->setItem($slot, $newItem);
                    }
                }	
	
                foreach ($player->getArmorInventory()->getContents() as $slot => $armor) {
                    if(!$armor instanceof Armor) return;
                    if ($armor->getDamage() > 0) {
                        $newArmor = $armor->setDamage(0); $player->getArmorInventory()->setItem($slot, $newArmor);
                    }
                }
                $sender->sendMessage(TextFormat::colorize('&aYou have fixed the items and the armor to the player ' . $player->getName()));
                $player->sendMessage(TextFormat::colorize('&aSomeone fixed your items and armor successfully'));
                break;
            
            default:
                $sender->sendMessage(
                    TextFormat::colorize('&eFix commands') . "\n" .
                    TextFormat::colorize('&7/fix hand - &eFix the item you have in your hand') . "\n" .
                    TextFormat::colorize('&7/fix all - &eFix all the items in your inventory and your armor') . "\n" .
                    TextFormat::colorize('&7/fix all [player] - &eFixes all items in a player\'s inventory and armor')
                );
                break;
    	}
    }
}