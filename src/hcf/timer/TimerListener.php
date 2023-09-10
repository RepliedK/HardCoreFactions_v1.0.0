<?php

declare(strict_types=1);

namespace hcf\timer;

use hcf\HCFLoader;
use hcf\player\disconnected\DisconnectedMob;
use hcf\player\Player;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;

/**
 * Class EventListener
 * @package hcf\timer
 */
class TimerListener implements Listener
{
    
    /**
     * @param EntityDamageEvent $event
     */
    public function handleDamage(EntityDamageEvent $event): void
    {
        $entity = $event->getEntity();
        
        if ($event->isCancelled()) return;
        
        if ($entity instanceof Player || $entity instanceof DisconnectedMob) {
            if (HCFLoader::getInstance()->getTimerManager()->getSotw()->isActive())
                $event->cancel();
        }
    }
}