<?php

declare(strict_types=1);

namespace hcf\entity;

use hcf\entity\custom\CustomItemEntity;
use hcf\entity\custom\TextEntity;
use hcf\entity\default\EnderpearlEntity;
use hcf\entity\default\SplashPotionEntity;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\object\ItemEntity;
use pocketmine\item\Item;
use pocketmine\item\PotionType;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\world\World;

/**
 * Class EntityManager
 * @package hcf\entity
 */
class EntityManager
{
    
    /**
     * EntityManager construct.
     */
    public function __construct()
    {
        EntityFactory::getInstance()->register(CustomItemEntity::class, function(World $world, CompoundTag $nbt) : ItemEntity{
            $itemTag = $nbt->getCompoundTag("Item");
            if($itemTag === null){
                throw new SavedDataLoadingException("Expected \"Item\" NBT tag not found");
            }

            $item = Item::nbtDeserialize($itemTag);
            if($item->isNull()){
                throw new SavedDataLoadingException("Item is invalid");
            }
            return new CustomItemEntity(EntityDataHelper::parseLocation($nbt, $world), $item, $nbt);
        }, ['Item', 'minecraft:item'], EntityIds::ITEM);

        EntityFactory::getInstance()->register(TextEntity::class, function (World $world, CompoundTag $nbt): TextEntity {
            return new TextEntity(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ['TextEntity', 'minecraft:textentity'], EntityIds::BAT);

        EntityFactory::getInstance()->register(EnderpearlEntity::class, function(World $world, CompoundTag $nbt): EnderpearlEntity {
            return new EnderpearlEntity(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        }, ['ThrownEnderpearl', 'minecraft:ender_pearl'], EntityIds::ENDER_PEARL);
        EntityFactory::getInstance()->register(SplashPotionEntity::class, function(World $world, CompoundTag $nbt): SplashPotionEntity {
            return new SplashPotionEntity(EntityDataHelper::parseLocation($nbt, $world), null, PotionType::STRONG_HEALING(), $nbt);
        }, ['ThrownPotion', 'minecraft:splash_potion'], EntityIds::SPLASH_POTION);
    }
}