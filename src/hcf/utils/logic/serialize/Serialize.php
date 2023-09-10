<?php

namespace hcf\utils\logic\serialize;

use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;

class Serialize {
    
    public static function getItem(string $item): Item {
        return StringToItemParser::getInstance()->parse($item);
    }

    public static function serialize(Item $item) : string {
        $itemToJson = self::itemToJson($item);
        return base64_encode(gzcompress($itemToJson));
    }

    public static function deserialize(string $item): Item {
        $itemFromJson = gzuncompress(base64_decode($item));
        return self::jsonToItem($itemFromJson);
    }

    public static function itemToJson(Item $item) : string {
        $cloneItem = clone $item;
        $itemNBT = $cloneItem->nbtSerialize();
        return base64_encode(serialize($itemNBT));
    }

    public static function jsonToItem(string $json) : Item {
        $itemNBT = unserialize(base64_decode($json));
        return Item::nbtDeserialize($itemNBT);
    }

}