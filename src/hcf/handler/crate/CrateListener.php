<?php

declare(strict_types=1);

namespace hcf\handler\crate;

use hcf\handler\crate\form\CrateForm;
use hcf\handler\crate\tile\CrateTile;
use hcf\HCFLoader;
use hcf\handler\crate\form\Forms;

use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\tile\Chest;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\utils\TextFormat;

/**
 * Class CrateListener
 * @package hcf\handler\crate
 */
class CrateListener implements Listener
{

    /**
     * @param BlockBreakEvent $event
     */
    public function handleBreak(BlockBreakEvent $event): void
    {
        $block = $event->getBlock();
        $player = $event->getPlayer();
        $tile = $player->getWorld()->getTile($block->getPosition()->asVector3());

        if ($tile instanceof CrateTile)
            $event->cancel();
    }

    /**
     * @param PlayerInteractEvent $event
     */
    public function handleInteract(PlayerInteractEvent $event): void
    {
        $action = $event->getAction();
        $block = $event->getBlock();
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();
        $itemInHand = $player->getInventory()->getItemInHand();
        if ($itemInHand->hasNamedTag() && $itemInHand->getNamedTag()->getTag('crate_name') !== null) {
            $crate = HCFLoader::getInstance()->getHandlerManager()->getCrateManager()->getCrate($itemInHand->getNamedTag()->getString('crate_name'));
            if ($crate !== null){
                $crate->giveReward($player);
        	}
    	}
        if ($block->getTypeId() == BlockTypeIds::CHEST) {
            $tile = $player->getWorld()->getTile($block->getPosition()->asVector3());

            if ($tile instanceof CrateTile) {
                $event->cancel();

                if ($player->getInventory()->getItemInHand()->getNamedTag()->getTag('crate_configuration') === null) {
                    if ($action === PlayerInteractEvent::LEFT_CLICK_BLOCK) {
                        $tile->openCratePreview($player);
                    } elseif ($action === PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
                        $tile->reedemKey($player);
                    }
                } else $tile->openCrateConfiguration($player);
                return;
            }

            if ($tile instanceof Chest) {
                if ($player->getInventory()->getItemInHand()->getNamedTag()->getTag('crate_configuration') !== null) {
                    $event->cancel();
                    CrateForm::createCreateTile($player, $block->getPosition());
                    return;
                }

                if ($action === PlayerInteractEvent::RIGHT_CLICK_BLOCK && $item->getNamedTag()->getTag('crate_place') !== null) {
                    $crateName = $item->getNamedTag()->getString('crate_place');
                    $event->cancel();

                    if (HCFLoader::getInstance()->getHandlerManager()->getCrateManager()->getCrate($crateName) === null) return;

                    $tilePosition = $block->getPosition()->asVector3();
                    $tile->close();

                    $newTile = new CrateTile($player->getWorld(), $tilePosition);
                    $newTile->setCrateName($crateName);
                    $player->getWorld()->addTile($newTile);

                    $player->sendMessage(TextFormat::colorize('&aYou have created the crate ' . $crateName . ' successfully'));
                }
            }
        }
    }
}