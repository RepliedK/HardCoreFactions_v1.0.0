<?php

namespace hcf\module\package;

use pocketmine\item\Item;

/**
 * Class PartnerPackage
 * @package PartnerPackage\module
 */
class PartnerPackage
{

    /** @var array|null */
    public array $items = [];

    /**
     * PartnerPackage constructor.
     * @param array|null $items
     */
    public function __construct(?array $items = [])
    {
        $this->items = $items;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function setItems(array $array): void
    {
        $this->items = $array;
    }

    public function getRandomItem(): Item
    {
        return $this->items[array_rand($this->items)];
    }
}