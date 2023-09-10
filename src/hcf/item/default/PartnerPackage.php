<?php

namespace hcf\item\default;

use hcf\player\Player;
use hcf\module\package\PackageManager;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;

class PartnerPackage implements Listener {

    public static function addPartner(Player $player, int $int)
    {
        $partner = VanillaBlocks::ENDER_CHEST()->asItem()->setCount($int);
        $partner->setCustomName(TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "§r§l§6Partner Packages");
        $partner->setLore(["\n§r§7Right click to receive different types of Ability Items.\n"]);
        $namedtag = $partner->getNamedTag();
        $namedtag->setString('pp_packages', 'pp');
        $partner->setNamedTag($namedtag);
        $player->getInventory()->addItem($partner);
    }

    public function handlePlace(BlockPlaceEvent $event)
    {
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();
        
        if ($item->getNamedTag()->getTag('pp_packages') !== null)
            $event->cancel();
    }

    public function onPlayerItemUse(PlayerItemUseEvent $event) : void {
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();
        $player = $event->getPlayer();
        $item = $event->getItem();
        if ($item->getNamedTag()->getTag('pp_packages') !== null) {
            $event->cancel();
            
            if (count(PackageManager::getPartnerPackage()->getItems()) == 0)
                return;
                    
            if (!$player->getInventory()->canAddItem(PackageManager::getPartnerPackage()->getRandomItem())) {
                $player->sendMessage(TextFormat::RED . "Your inventory is full!");
                return;
            }

            $item1 = PackageManager::getPartnerPackage()->getRandomItem();
            $item2 = PackageManager::getPartnerPackage()->getRandomItem();
            $player->getInventory()->addItem($item1);
            $player->getInventory()->addItem($item2);
            $player->sendMessage(" ");
            $player->sendMessage("§l§d      PARTNER PACKAGES     ");
            $player->sendMessage("§fYou have won " . $item1->getCustomName());
            $player->sendMessage("§fYou have won " . $item2->getCustomName());
            $player->sendMessage(" ");
            if($item->getCount() > 1){
                $item->setCount($item->getCount() - 1);
            }else{
                $item = VanillaItems::AIR();
            }
            $player->getInventory()->setItemInHand($item);
        }
    }

}