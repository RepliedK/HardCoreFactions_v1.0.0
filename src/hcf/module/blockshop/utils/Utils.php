<?php

declare(strict_types=1);

namespace hcf\module\blockshop\utils;

use hcf\HCFLoader;
use hcf\player\Player;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;

final class Utils
{
    
    /**
     * @param Player $player
     * @return CompoundTag
     */
    public static function createBasicNBT(Player $player): CompoundTag
    {
        $nbt = CompoundTag::create()
                        ->setTag("Pos", new ListTag([
				new DoubleTag($player->getLocation()->x),
				new DoubleTag($player->getLocation()->y),
				new DoubleTag($player->getLocation()->z)
			]))
			->setTag("Motion", new ListTag([
				new DoubleTag($player->getMotion()->x),
				new DoubleTag($player->getMotion()->y),
				new DoubleTag($player->getMotion()->z)
			]))
			->setTag("Rotation", new ListTag([
				new FloatTag($player->getLocation()->yaw),
				new FloatTag($player->getLocation()->pitch)
			]));
        return $nbt;
    }

    /**
     * @param Player $player
     */
    public static function openBlockShop(Player $player): void
    {
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
        $menu->getInventory()->setContents([
            0 => self::getItem(241, 1),
            1 => self::getItem(241, 1),
            7 => self::getItem(241, 1),
            8 => self::getItem(241, 1),
            9 => self::getItem(241, 1),
            17 => self::getItem(241, 1),
            36 => self::getItem(241, 1),
            44 => self::getItem(241, 1),
            45 => self::getItem(241, 1),
            46 => self::getItem(241, 1),
            52 => self::getItem(241, 1),
            53 => self::getItem(241, 1)
        ]);
        
        $menu->getInventory()->setItem(12, self::getItem(86)->setCustomName(TextFormat::colorize('&l&6Halloween Blocks')));
        $menu->getInventory()->setItem(13, self::getItem(114)->setCustomName(TextFormat::colorize('&l&6Nether Blocks')));
        $menu->getInventory()->setItem(14, self::getItem(79)->setCustomName(TextFormat::colorize('&l&6Winter Blocks')));
        
        $menu->getInventory()->setItem(21, self::getItem(241, 8)->setCustomName(TextFormat::colorize('&l&6Stained Glass Blocks')));
        $menu->getInventory()->setItem(22, self::getItem(155)->setCustomName(TextFormat::colorize('&l&6Quartz Blocks')));
        $menu->getInventory()->setItem(23, self::getItem(32)->setCustomName(TextFormat::colorize('&l&6Bush Blocks')));
        
        $menu->getInventory()->setItem(30, self::getItem(121)->setCustomName(TextFormat::colorize('&l&6End Blocks')));
        $menu->getInventory()->setItem(31, self::getItem(108)->setCustomName(TextFormat::colorize('&l&6Stone Blocks')));
        $menu->getInventory()->setItem(32, self::getItem(159)->setCustomName(TextFormat::colorize('&l&6Clay Blocks')));
        
        $menu->getInventory()->setItem(39, self::getItem(37)->setCustomName(TextFormat::colorize('&l&6Flower Blocks')));
        $menu->getInventory()->setItem(40, self::getItem(35)->setCustomName(TextFormat::colorize('&l&6Wool Blocks')));
        
        $menu->setListener(function (InvMenuTransaction $transaction): InvMenuTransactionResult {
            $player = $transaction->getPlayer();
            $item = $transaction->getItemClicked();
            $type = TextFormat::clean($item->getCustomName());
            
            if (self::getPage($type) !== null) {
                self::openPageBlockShop($player, $type);
                $player->removeCurrentWindow();
            }
            return $transaction->discard();
        });
        $menu->send($player, TextFormat::colorize('&7Block Shop'));
    }
    
    /**
     * @param Player $player
     * @param string $type
     */
    public static function openPageBlockShop(Player $player, string $type): void
    {
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
        $menu->getInventory()->setContents([
            0 => self::getItem(241, 1),
            1 => self::getItem(241, 1),
            7 => self::getItem(241, 1),
            8 => self::getItem(241, 1),
            9 => self::getItem(241, 1),
            17 => self::getItem(241, 1),
            36 => self::getItem(241, 1),
            44 => self::getItem(241, 1),
            45 => self::getItem(241, 1),
            46 => self::getItem(241, 1),
            52 => self::getItem(241, 1),
            53 => self::getItem(241, 1)
        ] + self::getPage($type));
        
        $menu->setListener(function (InvMenuTransaction $transaction): InvMenuTransactionResult {
            /** @var Player $player */
            $player = $transaction->getPlayer();
            $item = $transaction->getItemClicked();
            
            if ($item->getNamedTag()->getTag('price') !== null) {
                $newItem = $item->setLore([]);
                $newBalance = $player->getSession()->getBalance() - $item->getNamedTag()->getInt('price');

                if ($newBalance < 0) {
                    $player->sendMessage(TextFormat::colorize('&cYou do not have money to buy this item'));
                    return $transaction->discard();
                }
                
                if ($player->getInventory()->canAddItem($newItem)) {
                    $player->getInventory()->addItem($newItem);
                    $player->getSession()->setBalance($newBalance);
                } else $player->sendMessage(TextFormat::colorize('&cThis item cannot be stored in your inventory'));
            }
            return $transaction->discard();
        });
        $menu->setInventoryCloseListener(function (Player $player, $inventory) {
            HCFLoader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player): void {
                if ($player->isOnline())
                    self::openBlockShop($player);
            }), 1);
        });
        $menu->send($player, TextFormat::colorize('&7' . $type));
    }
    
    /**
     * @param Item $item
     * @param int $price
     * @return Item
     */
    private static function prepareItem(Item $item, int $price): Item
    {
        $item->setLore([TextFormat::colorize('&fPrice: &6$' . $price)]);
        
        $namedtag = $item->getNamedTag();
        $namedtag->setInt('price', $price);
        $item->setNamedTag($namedtag);
        
        return $item;
    }
    
    /**
     * @param string $type
     * @return array|null
     */
    private static function getPage(string $type): ?array
    {
        switch ($type) {
            case 'Halloween Blocks':
                return [
                    22 => self::prepareItem(self::getItem(86, 0, 16), 260),
                    31 => self::prepareItem(self::getItem(91, 0, 16), 260)
                ];
            
            case 'Nether Blocks':
                return [
                    12 => self::prepareItem(self::getItem(405, 0, 16), 80),
                    13 => self::prepareItem(self::getItem(113, 0, 16), 80),
                    14 => self::prepareItem(self::getItem(114, 0, 16), 80)
                ];
                
            case 'Winter Blocks':
                return [
                    12 => self::prepareItem(self::getItem(80, 0, 16), 125),
                    13 => self::prepareItem(self::getItem(174, 0, 16), 125),
                    14 => self::prepareItem(self::getItem(78, 0, 16), 125),
                    21 => self::prepareItem(self::getItem(79, 0, 16), 125)
                ];
                
            case 'Stained Glass Blocks':
                return [
                    12 => self::prepareItem(self::getItem(241, 0, 16), 300),
                    13 => self::prepareItem(self::getItem(241, 1, 16), 300),
                    14 => self::prepareItem(self::getItem(241, 2, 16), 300),
                    20 => self::prepareItem(self::getItem(241, 3, 16), 300),
                    21 => self::prepareItem(self::getItem(241, 4, 16), 300),
                    22 => self::prepareItem(self::getItem(241, 5, 16), 300),
                    23 => self::prepareItem(self::getItem(241, 6, 16), 300),
                    24 => self::prepareItem(self::getItem(241, 7, 16), 300),
                    29 => self::prepareItem(self::getItem(241, 8, 16), 300),
                    30 => self::prepareItem(self::getItem(241, 9, 16), 300),
                    31 => self::prepareItem(self::getItem(241, 10, 16), 300),
                    32 => self::prepareItem(self::getItem(241, 11, 16), 300),
                    33 => self::prepareItem(self::getItem(241, 12, 16), 300),
                    39 => self::prepareItem(self::getItem(241, 13, 16), 300),
                    40 => self::prepareItem(self::getItem(241, 14, 16), 300),
                    41 => self::prepareItem(self::getItem(241, 15, 16), 300)
                ];
                
            case 'Quartz Blocks':
                return [
                    22 => self::prepareItem(self::getItem(406, 0, 16), 225),
                    31 => self::prepareItem(self::getItem(156, 0, 16), 225)
                ];
                
            case 'Bush Blocks':
                return [
                    22 => self::prepareItem(self::getItem(32, 0, 16), 200),
                    31 => self::prepareItem(self::getItem(2, 0, 16), 200)
                ];
                
            case 'End Blocks':
                return [
                    22 => self::prepareItem(self::getItem(121, 0, 16), 5000)
                ];
                
            case 'Stone Blocks':
                return [
                    21 => self::prepareItem(self::getItem(67, 0, 16), 250),
                    22 => self::prepareItem(self::getItem(98, 0, 16), 250),
                    23 => self::prepareItem(self::getItem(109, 0, 16), 250),
                    30 => self::prepareItem(self::getItem(48, 0, 16), 250),
                    31 => self::prepareItem(self::getItem(1, 0, 16), 250)
                ];
                
            case 'Clay Blocks':
                return [
                    12 => self::prepareItem(self::getItem(159, 0, 16), 300),
                    13 => self::prepareItem(self::getItem(159, 1, 16), 300),
                    14 => self::prepareItem(self::getItem(159, 2, 16), 300),
                    20 => self::prepareItem(self::getItem(159, 3, 16), 300),
                    21 => self::prepareItem(self::getItem(159, 4, 16), 300),
                    22 => self::prepareItem(self::getItem(159, 5, 16), 300),
                    23 => self::prepareItem(self::getItem(159, 6, 16), 300),
                    24 => self::prepareItem(self::getItem(159, 7, 16), 300),
                    29 => self::prepareItem(self::getItem(159, 8, 16), 300),
                    30 => self::prepareItem(self::getItem(159, 9, 16), 300),
                    31 => self::prepareItem(self::getItem(159, 10, 16), 300),
                    32 => self::prepareItem(self::getItem(159, 11, 16), 300),
                    33 => self::prepareItem(self::getItem(159, 12, 16), 300),
                    39 => self::prepareItem(self::getItem(159, 13, 16), 300),
                    40 => self::prepareItem(self::getItem(159, 14, 16), 300),
                    41 => self::prepareItem(self::getItem(159, 15, 16), 300)
                ];
                
            case 'Flower Blocks':
                return [
                    21 => self::prepareItem(self::getItem(37, 0, 16), 250),
                    22 => self::prepareItem(self::getItem(38, 0, 16), 250),
                    23 => self::prepareItem(self::getItem(37, 0, 16), 250)
                ];
                
            case 'Wool Blocks':
                return [
                    12 => self::prepareItem(self::getItem(35, 0, 16), 300),
                    13 => self::prepareItem(self::getItem(35, 1, 16), 300),
                    14 => self::prepareItem(self::getItem(35, 2, 16), 300),
                    20 => self::prepareItem(self::getItem(35, 3, 16), 300),
                    21 => self::prepareItem(self::getItem(35, 4, 16), 300),
                    22 => self::prepareItem(self::getItem(35, 5, 16), 300),
                    23 => self::prepareItem(self::getItem(35, 6, 16), 300),
                    24 => self::prepareItem(self::getItem(35, 7, 16), 300),
                    29 => self::prepareItem(self::getItem(35, 8, 16), 300),
                    30 => self::prepareItem(self::getItem(35, 9, 16), 300),
                    31 => self::prepareItem(self::getItem(35, 10, 16), 300),
                    32 => self::prepareItem(self::getItem(35, 11, 16), 300),
                    33 => self::prepareItem(self::getItem(35, 12, 16), 300),
                    39 => self::prepareItem(self::getItem(35, 13, 16), 300),
                    40 => self::prepareItem(self::getItem(35, 14, 16), 300),
                    41 => self::prepareItem(self::getItem(35, 15, 16), 300)
                ];
        }
        return null;
    }

    public static function sellShop(Player $player) : void {
    
        $glass = VanillaBlocks::GLASS()->asItem();
        $glass->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(4), 1));
        
        $gold = VanillaBlocks::GOLD()->asItem()->setCount(16);
        $gold->setCustomName(TextFormat::colorize("&eGold Block\nPrice: &41250"));
        
        $diamond = VanillaBlocks::DIAMOND()->asItem()->setCount(16);
        $diamond->setCustomName(TextFormat::colorize("&eDiamond Block\nPrice: &41800"));
        $emerald = VanillaBlocks::EMERALD()->asItem()->setCount(16);
        $emerald->setCustomName(TextFormat::colorize("&eEmerald Block\nPrice: &42000"));
        $redstone = VanillaBlocks::REDSTONE()->asItem()->setCount(16);
        $redstone->setCustomName(TextFormat::colorize("&eRedstone Block\nPrice: &4600"));
        $lapiz = VanillaBlocks::LAPIS_LAZULI()->asItem()->setCount(16);
        $lapiz->setCustomName(TextFormat::colorize("&eLapis Block\nPrice: &41800"));
        $iron = VanillaBlocks::IRON()->asItem()->setCount(16);
        $iron->setCustomName(TextFormat::colorize("&eIron Block\nPrice: &4800"));
        $coal = VanillaBlocks::COAL()->asItem()->setCount(16);
        $coal->setCustomName(TextFormat::colorize("&eCoal Block\nPrice: &4300"));
        
        
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST);
        
        $menu->setName("§7Sell Shop");
        $menu->getInventory()->setContents([
          0=> $glass,
          1=> $glass,
          9=> $glass,
          18=> $glass,
          19=> $glass,
          7=> $glass,
          8=> $glass,
          17=> $glass,
          25=> $glass,
          26=> $glass,
    
          3=> $gold,
          4=> $diamond,
          5=> $emerald,
          12=> $redstone,
          13=> $lapiz,
          14=> $iron,
          22=> $coal,
          ]);
          
           $menu->setListener(function (InvMenuTransaction $transaction): InvMenuTransactionResult {
             
            $player = $transaction->getPlayer();
            
            IF (!$player instanceof Player) return $transaction->discard();
            if ($transaction->getItemClicked()->getCustomName() === "§eGold Block\nPrice: §41250") {
              $item = null;
              
              foreach ($player->getInventory()->getContents() as $playerInventory)
              
              if ($playerInventory->equals(VanillaBlocks::GOLD()->asItem()))
              
              $item = $playerInventory;
              
              if ($item === null) {
                $player->sendMessage(TextFormat::colorize("&cYou need 16 or more blocks to be able to sell them"));
                return $transaction->discard();
                    }
                    if ($item->getCount() >= 16) {
                      $player->getInventory()->removeItem($item->setCount(16));
                      $player->getSession()->setBalance($player->getSession()->getBalance() + 1250);
                      $player->sendMessage(TextFormat::colorize("&aYou just sell this item"));
                    }
    
                    if ($item->getCount() < 16) {
                      $player->sendMessage(TextFormat::colorize("&cYou need 16 or more blocks to be able to sell them"));
                    }
                }
                if ($transaction->getItemClicked()->getCustomName() === "§eDiamond Block\nPrice: §41800") {
              $item = null;
              
              foreach ($player->getInventory()->getContents() as $playerInventory)
              
              if ($playerInventory->equals(VanillaBlocks::DIAMOND()->asItem()))
              
              $item = $playerInventory;
              
              if ($item === null) {
                $player->sendMessage(TextFormat::colorize("&cYou need 16 or more blocks to be able to sell them"));
                return $transaction->discard();
                    }
                    if ($item->getCount() >= 16) {
                      $player->getInventory()->removeItem($item->setCount(16));
                      $player->getSession()->setBalance($player->getSession()->getBalance() + 1800);
                      $player->sendMessage(TextFormat::colorize("&aYou just sell this item"));
                    }
    
                    if ($item->getCount() < 16) {
                      $player->sendMessage(TextFormat::colorize("&cYou need 16 or more blocks to be able to sell them"));
                    }
                }
                 if ($transaction->getItemClicked()->getCustomName() === "§eEmerald Block\nPrice: §42000") {
              $item = null;
              
              foreach ($player->getInventory()->getContents() as $playerInventory)
              
              if ($playerInventory->equals(VanillaBlocks::EMERALD()->asItem()))
              
              $item = $playerInventory;
              
              if ($item === null) {
                $player->sendMessage(TextFormat::colorize("&cYou need 16 or more blocks to be able to sell them"));
                return $transaction->discard();
                    }
                    if ($item->getCount() >= 16) {
                      $player->getInventory()->removeItem($item->setCount(16));
                      $player->getSession()->setBalance($player->getSession()->getBalance() + 2000);
                      $player->sendMessage(TextFormat::colorize("&aYou just sell this item"));
                    }
    
                    if ($item->getCount() < 16) {
                      $player->sendMessage(TextFormat::colorize("&cYou need 16 or more blocks to be able to sell them"));
                    }
                }
                 if ($transaction->getItemClicked()->getCustomName() === "§eRedstone Block\nPrice: §4600") {
              $item = null;
              
              foreach ($player->getInventory()->getContents() as $playerInventory)
              
              if ($playerInventory->equals(VanillaBlocks::REDSTONE()->asItem()))
              
              $item = $playerInventory;
              
              if ($item === null) {
                $player->sendMessage(TextFormat::colorize("&cYou need 16 or more blocks to be able to sell them"));
                return $transaction->discard();
                    }
                    if ($item->getCount() >= 16) {
                      $player->getInventory()->removeItem($item->setCount(16));
                      $player->getSession()->setBalance($player->getSession()->getBalance() + 600);
                       $player->sendMessage(TextFormat::colorize("&aYou just sell this item"));
                    }
    
                    if ($item->getCount() < 16) {
                    $player->sendMessage(TextFormat::colorize("&cYou need 16 or more blocks to be able to sell them"));
                    }
                }
                 if ($transaction->getItemClicked()->getCustomName() === "§eLapis Block\nPrice: §41800") {
              $item = null;
              
              foreach ($player->getInventory()->getContents() as $playerInventory)
              
              if ($playerInventory->equals(VanillaBlocks::LAPIS_LAZULI()->asItem()))
              
              $item = $playerInventory;
              
              if ($item === null) {
                $player->sendMessage(TextFormat::colorize("&cYou need 16 or more blocks to be able to sell them"));
                return $transaction->discard();
                    }
                    if ($item->getCount() >= 16) {
                      $player->getInventory()->removeItem($item->setCount(16));
                      $player->getSession()->setBalance($player->getSession()->getBalance() + 1800);
                      $player->sendMessage(TextFormat::colorize("&aYou just sell this item"));
                    }
    
                    if ($item->getCount() < 16) {
                      $player->sendMessage(TextFormat::colorize("&cYou need 16 or more blocks to be able to sell them"));
                    }
                }
                 if ($transaction->getItemClicked()->getCustomName() === "§eIron Block\nPrice: §4800") {
              $item = null;
              
              foreach ($player->getInventory()->getContents() as $playerInventory)
              
              if ($playerInventory->equals(VanillaBlocks::IRON()->asItem()))
              
              $item = $playerInventory;
              
              if ($item === null) {
                $player->sendMessage(TextFormat::colorize("&cYou need 16 or more blocks to be able to sell them"));
                return $transaction->discard();
                    }
                    if ($item->getCount() >= 16) {
                      $player->getInventory()->removeItem($item->setCount(16));
                      $player->getSession()->setBalance($player->getSession()->getBalance() + 800);
                      $player->sendMessage(TextFormat::colorize("&aYou just sell this item"));
                    }
    
                    if ($item->getCount() < 16) {
                      $player->sendMessage(TextFormat::colorize("&cYou need 16 or more blocks to be able to sell them"));
                    }
                }
                 if ($transaction->getItemClicked()->getCustomName() === "§eCoal Block\nPrice: §4300") {
              $item = null;
              
              foreach ($player->getInventory()->getContents() as $playerInventory)
              
              if ($playerInventory->equals(VanillaBlocks::COAL()->asItem()))
              
              $item = $playerInventory;
              
              if ($item === null) {
                $player->sendMessage(TextFormat::colorize("&cYou need 16 or more blocks to be able to sell them"));
                return $transaction->discard();
                    }
                    if ($item->getCount() >= 16) {
                      $player->getInventory()->removeItem($item->setCount(16));
                      $player->getSession()->setBalance($player->getSession()->getBalance() + 300);
                      $player->sendMessage(TextFormat::colorize("&aYou just sell this item"));
                    }
    
                    if ($item->getCount() < 16) {
                      $player->sendMessage(TextFormat::colorize("&cYou need 16 or more blocks to be able to sell them"));
                    }
                }
                return $transaction->discard();
              });
              $menu->send($player);
      }

    public static function getItem($id, $meta = 0, $count = 1): Item {
        return LegacyStringToItemParser::getInstance()->parse("{$id}:{$meta}")->setCount($count);
    }

}