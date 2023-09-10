<?php

declare(strict_types=1);

namespace hcf\utils\display;

use hcf\HCFLoader;
use hcf\player\Player;
use hcf\utils\display\Items;
use hcf\utils\logic\time\Timer;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\Inventory;
use pocketmine\utils\TextFormat;

/**
 * Class Inventories
 * @package hcf\utils
 */
final class Inventories
{

    public static function createCrateContent(Player $player, array $data): void
    {
        $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        $menu->setInventoryCloseListener(function (Player $player, Inventory $inventory) use ($data): void {
            $data['items'] = $inventory->getContents();
            HCFLoader::getInstance()->getHandlerManager()->getCrateManager()->addCrate($data['crateName'], $data['key'], $data['keyFormat'], $data['nameFormat'], (array) $data['items']);
            
            $chest = VanillaBlocks::CHEST()->asItem();
            $chest->setCustomName(TextFormat::colorize('Crate ' . $data['crateName']));
            $namedtag = $chest->getNamedTag();
            $namedtag->setString('crate_place', $data['crateName']);
            $chest->setNamedTag($namedtag);
            
            $player->getInventory()->addItem($chest);
            $player->sendMessage(TextFormat::colorize('&aYou have successfully created the crate ' . $data['crateName']));
        });
        $menu->send($player, TextFormat::colorize('&4Crate content'));
    }
    
    public static function editCrateContent(Player $player, string $crateName): void
    {
        $crate = HCFLoader::getInstance()->getHandlerManager()->getCrateManager()->getCrate($crateName);

        $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        $menu->getInventory()->setContents($crate?->getItems());
        $menu->setInventoryCloseListener(function (Player $player, Inventory $inventory) use ($crate): void {
            $crate?->setItems($inventory->getContents());
            $player->sendMessage(TextFormat::colorize('&aYou have edited the content'));
        });
        $menu->send($player, TextFormat::colorize('&4Edit crate'));
    }
    
    /**
     * @param Player $player
     */
    public static function createKitOrganization(Player $player): void
    {
        $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        $organization = HCFLoader::getInstance()->getHandlerManager()->getKitManager()->getOrganization();
        
        for ($i = 0; $i < 54; $i++) {
            if (isset($organization[$i])) {
                $kit = HCFLoader::getInstance()->getHandlerManager()->getKitManager()->getKit($organization[$i]);
                
                if ($kit !== null)
                    $menu->getInventory()->setItem($i, Items::createItemKitOrganization($player, $kit->getRepresentativeItem(), $kit->getName()));
                else $menu->getInventory()->setItem($i, VanillaBlocks::AIR()->asItem());
            } else
                $menu->getInventory()->setItem($i, VanillaBlocks::AIR()->asItem());
        }
        $menu->setListener(function (InvMenuTransaction $transaction): InvMenuTransactionResult {
            /** @var Player */
            $player = $transaction->getPlayer();
            $item = $transaction->getItemClicked();
            
            if ($item->getNamedTag()->getTag('kit_name') !== null) {
                $kit = HCFLoader::getInstance()->getHandlerManager()->getKitManager()->getKit($item->getNamedTag()->getString('kit_name'));
                
                if ($kit !== null) {
                    
                    # Permission
                    if ($kit->getPermission() !== null && !$player->hasPermission($kit->getPermission())) {
                        $player->sendMessage(TextFormat::colorize('&cYou do not have permission to use the kit'));
                        return $transaction->discard();
                    }
                    
                    # Cooldown
                    if ($player->getSession()->getCooldown('kit.' . $kit->getName()) !== null) {
                        $player->sendMessage(TextFormat::colorize('&cYou have kit cooldown. Time remaining ' . Timer::convert($player->getSession()->getCooldown('kit.' . $kit->getName())->getTime())));
                        return $transaction->discard();
                    }

                    # Give kit
                    $kit->giveTo($player);
                    
                    # Add cooldown
                    if ($kit->getCooldown() !== 0)
                        $player->getSession()->addCooldown('kit.' . $kit->getName(), '', $kit->getCooldown(), false, false);
                    
                    $player->removeCurrentWindow();
                }
            }
            return $transaction->discard();
        });
        $menu->send($player, TextFormat::colorize('&eKits'));
    }
    
    /**
     * @param Player $player
     */
    public static function editKitOrganization(Player $player): void
    {
        $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        
        foreach (HCFLoader::getInstance()->getHandlerManager()->getKitManager()->getOrganization() as $slot => $kitName) {
            $kit = HCFLoader::getInstance()->getHandlerManager()->getKitManager()->getKit($kitName);
            
            if ($kit !== null) $menu->getInventory()->setItem($slot, Items::createItemKitOrganization($player, $kit->getRepresentativeItem(), $kit->getName()));
        }
        $menu->setListener(function (InvMenuTransaction $transaction): InvMenuTransactionResult {
            /** @var Player */
            $player = $transaction->getPlayer();
            $item = $transaction->getItemClickedWith();
            
            if (!$item->isNull() && $item->getNamedTag()->getTag('kit_name') === null)
                return $transaction->discard();
            return $transaction->continue();
        });
        $menu->setInventoryCloseListener(function (Player $player, Inventory $inventory): void {
            $data = [];
            $contents = $inventory->getContents();
            
            foreach ($contents as $slot => $item) {
                $kit = HCFLoader::getInstance()->getHandlerManager()->getKitManager()->getKit($item->getNamedTag()->getString('kit_name'));
                
                if ($kit !== null) $data[$slot] = $kit->getName();
            }
            HCFLoader::getInstance()->getHandlerManager()->getKitManager()->setOrganization($data);
        });
        $menu->send($player, TextFormat::colorize('&6Edit kit organization'));
    }
}