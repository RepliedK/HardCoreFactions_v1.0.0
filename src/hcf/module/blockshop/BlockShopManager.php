<?php

declare(strict_types=1);

namespace hcf\module\blockshop;

use hcf\HCFLoader;
use hcf\module\blockshop\command\BlockShopCommand;
use hcf\module\blockshop\entity\BlockShopEntity;
use hcf\module\blockshop\entity\SellShopEntity;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;

/**
 * Class BlockShop
 * @package hcf\blockshop
 */
class BlockShopManager
{
    
    public function __construct(){
        EntityFactory::getInstance()->register(BlockShopEntity::class, function (World $world, CompoundTag $nbt): BlockShopEntity {
            return new BlockShopEntity(EntityDataHelper::parseLocation($nbt, $world), BlockShopEntity::parseSkinNBT($nbt), $nbt);
        }, ['BlockShopEntity']);
        EntityFactory::getInstance()->register(SellShopEntity::class, function (World $world, CompoundTag $nbt): SellShopEntity {
            return new SellShopEntity(EntityDataHelper::parseLocation($nbt, $world), SellShopEntity::parseSkinNBT($nbt), $nbt);
        }, ['SellShopEntity']);
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('BlockShop', new BlockShopCommand());
    }
    
}