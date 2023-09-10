<?php

declare(strict_types=1);

namespace hcf\handler\kit\classes\presets;

use hcf\handler\kit\classes\ClassFactory;
use hcf\handler\kit\classes\HCFClass;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;

/**
 * Class Mage
 * @package hcf\handler\kit\classes\presets
 */
class Mage extends HCFClass
{

    /**
     * Mage construct.
     */
    public function __construct()
    {
        parent::__construct(self::MAGE);
    }

    /**
     * @return Item[]
     */
    public function getArmorItems(): array
    {
        return [
            VanillaItems::GOLDEN_HELMET(),
            VanillaItems::CHAINMAIL_CHESTPLATE(),
            VanillaItems::CHAINMAIL_LEGGINGS(),
            VanillaItems::GOLDEN_BOOTS()
        ];
    }

    /**
     * @return EffectInstance[]
     */
    public function getEffects(): array
    {
        return ClassFactory::getClassById(self::ARCHER)->getEffects();
    }
}