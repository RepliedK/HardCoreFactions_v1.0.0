<?php

declare(strict_types=1);

namespace hcf\utils\display;

use hcf\HCFLoader;
use hcf\player\Player;
use hcf\utils\logic\time\Timer;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\utils\TextFormat;

/**
 * Class Items
 * @package hcf\utils
 */
final class Items
{
    
    /** @var string[] */
    private static $kitLore = [
        '&7Choose this kit to play',
        '&7It will help you in your progress',
        '&r',
        '&eCooldown: &f{kit_cooldown}',
        '&eAvailable in: &f{player_cooldown}'
    ];
    
    /**
     * @param Player $player
     * @param Item $item
     * @param string $kitName
     * @return Item
     */
    public static function createItemKitOrganization(Player $player, Item $item, string $kitName): Item
    {
        $kit = HCFLoader::getInstance()->getHandlerManager()->getKitManager()->getKit($kitName);
        
        $item->setCustomName(TextFormat::colorize($kit->getNameFormat()));
        $item->setLore(array_map(function (mixed $text) use ($player, $kit) {
            $player_cooldown = $player->getSession()->getCooldown('kit.' . $kit->getName()) !== null ? Timer::convert($player->getSession()->getCooldown('kit.' . $kit->getName())->getTime()) : 'N/A';
            $kit_cooldown = $kit->getCooldown() !== 0 ? Timer::convert($kit->getCooldown()) : 'N/A';
            $text = str_replace(['{player_cooldown}', '{kit_cooldown}'], [$player_cooldown, $kit_cooldown], $text);
            return TextFormat::colorize($text);
        }, self::$kitLore));
        
        $namedtag = $item->getNamedTag();
        $namedtag->setString('kit_name', $kitName);
        $item->setNamedTag($namedtag);
        
        return $item;
    }
    
}