<?php

declare(strict_types=1);

namespace hcf\entity\custom;

use pocketmine\entity\object\ItemEntity;

class CustomItemEntity extends ItemEntity
{
    protected float $gravity = 0.0;
    protected bool $immobile = true;

    /**
     * @return bool
     */
    public function canBeMovedByCurrents(): bool
    {
        return false;
    }

    public function onNearbyBlockChange(): void
    {
        $this->setForceMovementUpdate();
        $this->scheduleUpdate();
    }
}

