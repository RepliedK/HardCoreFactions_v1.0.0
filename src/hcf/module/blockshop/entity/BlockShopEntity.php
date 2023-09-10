<?php

declare(strict_types=1);

namespace hcf\module\blockshop\entity;

use hcf\player\Player;
use hcf\module\blockshop\utils\Utils;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\TextFormat;

class BlockShopEntity extends Human
{

    /**
     * @param CompoundTag $nbt
     */
    public function initEntity(CompoundTag $nbt): void
    {
        parent::initEntity($nbt);
        $this->setForceMovementUpdate(false);
        $this->setNameTagAlwaysVisible(true);
        $this->setNameTag(TextFormat::colorize("&l&a × NEW ×\n&r&6Block Shop\n&r&7(Attack To Open Menu)"));
    }

    /**
     * @param EntityDamageEvent $source
     */
    public function attack(EntityDamageEvent $source): void
    {
        $source->cancel();

        if ($source instanceof EntityDamageByEntityEvent) {
            $damager = $source->getDamager();

            if ($damager instanceof Player) {
                if ($damager->hasPermission('remove.npc.blockshop') && $damager->getInventory()->getItemInHand()->getTypeId() === VanillaBlocks::BEDROCK()->asItem()->getTypeId()) {
                    $this->kill();
                    return;
                }
                Utils::openBlockShop($damager);
            }
        }
    }
}