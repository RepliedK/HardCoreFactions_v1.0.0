<?php

declare(strict_types=1);

namespace hcf\handler\kit\classes\presets;

use hcf\handler\kit\classes\HCFClass;
use hcf\player\Player;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;

class Bard extends HCFClass
{

    /**
     * Bard construct.
     */
    public function __construct()
    {
        parent::__construct(self::BARD);
    }

    /**
     * @return Item[]
     */
    public function getArmorItems(): array
    {
        return [
            VanillaItems::GOLDEN_HELMET(),
            VanillaItems::GOLDEN_CHESTPLATE(),
            VanillaItems::GOLDEN_LEGGINGS(),
            VanillaItems::GOLDEN_BOOTS()
        ];
    }

    /**
     * @return EffectInstance[]
     */
    public function getEffects(): array
    {
        return [
            new EffectInstance(VanillaEffects::SPEED(), 20 * 15, 1),
            new EffectInstance(VanillaEffects::RESISTANCE(), 20 * 15, 0),
            new EffectInstance(VanillaEffects::REGENERATION(), 20 * 15, 0)
        ];
    }
    
    /**
     * @param PlayerItemHeldEvent $event
     */
    public function handleItemHeld(PlayerItemHeldEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();

        if ($player instanceof Player) {
            if ($player->getClass() === null)
                return;
                
            if ($player->getClass()->getTypeId() === HCFClass::BARD) {
                if ($player->getSession()->getCooldown('bard.cooldown') !== null)
                    return;
                    
                if ($player->getSession()->getCooldown('starting.timer') !== null || $player->getSession()->getCooldown('pvp.timer') !== null)
                    return;
            
                if ($player->getCurrentClaim() === 'Spawn')
                    return;
                    
                if ($item->getTypeId() === VanillaItems::MAGMA_CREAM()->getTypeId()) {
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::FIRE_RESISTANCE(), 20 * 7, 1));
                    
                    if ($player->getSession()->getFaction() !== null) {
                        $players = array_filter($player->getServer()->getOnlinePlayers(), function ($target) use ($player): bool {
                             return $target instanceof Player && $player->getPosition()->distance($target->getPosition()) <= 20 && $player->getSession()->getFaction() === $target->getSession()->getFaction();
                        });
                       
                        if (count($players) !== 0) {
                            foreach ($players as $target) {
                                $target->getEffects()->add(new EffectInstance(VanillaEffects::FIRE_RESISTANCE(), 20 * 7, 1));
                            }
                        }
                    }
                } elseif ($item->getTypeId() === VanillaItems::INK_SAC()->getTypeId()) {
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::INVISIBILITY(), 20 * 7, 0));
                    
                    if ($player->getSession()->getFaction() !== null) {
                        $players = array_filter($player->getServer()->getOnlinePlayers(), function ($target) use ($player): bool {
                             return $target instanceof Player && $player->getPosition()->distance($target->getPosition()) <= 20 && $player->getSession()->getFaction() === $target->getSession()->getFaction();
                        });
                        
                        if (count($players) !== 0) {
                            foreach ($players as $target) {
                                $target->getEffects()->add(new EffectInstance(VanillaEffects::INVISIBILITY(), 20 * 7, 0));
                            }
                        }
                    }
                } elseif ($item->getTypeId() === VanillaItems::BLAZE_POWDER()->getTypeId()) {
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 20 * 7, 0));
                    
                    if ($player->getSession()->getFaction() !== null) {
                        $players = array_filter($player->getServer()->getOnlinePlayers(), function ($target) use ($player): bool {
                             return $target instanceof Player && $player->getPosition()->distance($target->getPosition()) <= 20 && $player->getSession()->getFaction() === $target->getSession()->getFaction();
                        });
                       
                        if (count($players) !== 0) {
                            foreach ($players as $target) {
                                $target->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 20 * 7, 0));
                            }
                        }
                    }
                } elseif ($item->getTypeId() === VanillaItems::IRON_INGOT()->getTypeId()) {
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 20 * 7, 0));
                    
                    if ($player->getSession()->getFaction() !== null) {
                        $players = array_filter($player->getServer()->getOnlinePlayers(), function ($target) use ($player): bool {
                            return $target instanceof Player && $player->getPosition()->distance($target->getPosition()) <= 20 && $player->getSession()->getFaction() === $target->getSession()->getFaction();
                        });
                       
                       if (count($players) !== 0) {
                            foreach ($players as $target) {
                                $target->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 20 * 7, 0));
                            }
                        }
                    }
                } elseif ($item->getTypeId() === VanillaItems::SUGAR()->getTypeId()) {
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 20 * 7, 1));
                    
                    if ($player->getSession()->getFaction() !== null) {
                        $players = array_filter($player->getServer()->getOnlinePlayers(), function ($target) use ($player): bool {
                            return $target instanceof Player && $player->getPosition()->distance($target->getPosition()) <= 20 && $player->getSession()->getFaction() === $target->getSession()->getFaction();
                        });
                       
                        if (count($players) !== 0) {
                            foreach ($players as $target) {
                                $target->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 20 * 7, 1));
                            }
                        }
                    }
                } elseif ($item->getTypeId() === VanillaItems::FEATHER()->getTypeId()) {
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::JUMP_BOOST(), 20 * 7, 2));
                    
                    if ($player->getSession()->getFaction() !== null) {
                        $players = array_filter($player->getServer()->getOnlinePlayers(), function ($target) use ($player): bool {
                            return $target instanceof Player && $player->getPosition()->distance($target->getPosition()) <= 20 && $player->getSession()->getFaction() === $target->getSession()->getFaction();
                        });
                       
                        if (count($players) !== 0) {
                            foreach ($players as $target) {
                                $target->getEffects()->add(new EffectInstance(VanillaEffects::JUMP_BOOST(), 20 * 7, 2));
                            }
                        }
                    }
                } elseif ($item->getTypeId() === VanillaItems::GHAST_TEAR()->getTypeId()) {
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 20 * 7, 1));
                    
                    if ($player->getSession()->getFaction() !== null) {
                        $players = array_filter($player->getServer()->getOnlinePlayers(), function ($target) use ($player): bool {
                            return $target instanceof Player && $player->getPosition()->distance($target->getPosition()) <= 20 && $player->getSession()->getFaction() === $target->getSession()->getFaction();
                        });
                       
                        if (count($players) !== 0) {
                            foreach ($players as $target) {
                                $target->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 20 * 7, 1));
                            }
                        }
                    }
                }
            }
        }
    }
    
    /**
     * @param PlayerItemUseEvent $event
     */
    public function handleItemUse(PlayerItemUseEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();

        if ($player instanceof Player) {
            if ($player->getClass() === null)
                return;
                
            if ($player->getClass()->getTypeId() === HCFClass::BARD) {
                if ($player->getSession()->getEnergy('bard.energy') === null)
                    return;
                $energy = $player->getSession()->getEnergy('bard.energy');
                
                if ($player->getSession()->getCooldown('bard.cooldown') !== null)
                    return;
                    
                if ($player->getSession()->getCooldown('starting.timer') !== null || $player->getSession()->getCooldown('pvp.timer') !== null)
                    return;
            
                if ($player->getCurrentClaim() === 'Spawn')
                    return;
                    
                if ($item->getTypeId() === VanillaItems::SPIDER_EYE()->getTypeId()) {
                    if ($energy->getEnergy() < 35)
                        return;
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::WITHER(), 20 * 7, 1));
                    $players = array_filter($player->getServer()->getOnlinePlayers(), function ($target) use ($player): bool {
                         return $target instanceof Player && $player->getPosition()->distance($target->getPosition()) <= 20;
                    });
                       
                    if (count($players) !== 0) {
                        foreach ($players as $target) {
                            $target->getEffects()->add(new EffectInstance(VanillaEffects::WITHER(), 20 * 7, 1));
                            $target->sendMessage(TextFormat::colorize('&eThe bard (&a' . $player->getName() . '&e) has used &4Wither II'));
                        }
                    }
                    $item->pop();
                    $player->getInventory()->setItemInHand($item);
                    $player->getSession()->addCooldown('bard.cooldown', '&l&eBard Effect&r&7: &r&c', 10);
                    $energy->reduceEnergy(35);
                } elseif ($item->getTypeId() === VanillaItems::BLAZE_POWDER()->getTypeId()) {
                    if ($energy->getEnergy() < 40)
                        return;
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 20 * 7, 1));
                    
                    if ($player->getSession()->getFaction() !== null) {
                        $players = array_filter($player->getServer()->getOnlinePlayers(), function ($target) use ($player): bool {
                             return $target instanceof Player && $player->getPosition()->distance($target->getPosition()) <= 20 && $player->getSession()->getFaction() === $target->getSession()->getFaction();
                        });
                       
                        if (count($players) !== 0) {
                            foreach ($players as $target) {
                                $target->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 20 * 7, 1));
                                $target->sendMessage(TextFormat::colorize('&eThe bard in your faction (&a' . $player->getName() . '&e) has used &4Strenght II'));
                            }
                        }
                    }
                    $item->pop();
                    $player->getInventory()->setItemInHand($item);
                    $player->getSession()->addCooldown('bard.cooldown', '&l&eBard Effect&r&7: &r&c', 10);
                    $energy->reduceEnergy(40);
                } elseif ($item->getTypeId() === VanillaItems::IRON_INGOT()->getTypeId()) {
                    if ($energy->getEnergy() < 35)
                        return;
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 20 * 7, 2));
                    
                    if ($player->getSession()->getFaction() !== null) {
                        $players = array_filter($player->getServer()->getOnlinePlayers(), function ($target) use ($player): bool {
                            return $target instanceof Player && $player->getPosition()->distance($target->getPosition()) <= 20 && $player->getSession()->getFaction() === $target->getSession()->getFaction();
                        });
                       
                       if (count($players) !== 0) {
                            foreach ($players as $target) {
                                $target->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 20 * 7, 2));
                                $target->sendMessage(TextFormat::colorize('&eThe bard in your faction (&a' . $player->getName() . '&e) has used &4Resistance III'));
                            }
                        }
                    }
                    $item->pop();
                    $player->getInventory()->setItemInHand($item);
                    $player->getSession()->addCooldown('bard.cooldown', '&l&eBard Effect&r&7: &r&c', 10);
                    $energy->reduceEnergy(35);
                } elseif ($item->getTypeId() === VanillaItems::SUGAR()->getTypeId()) {
                    if ($energy->getEnergy() < 20)
                        return;
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 20 * 7, 2));
                    
                    if ($player->getSession()->getFaction() !== null) {
                        $players = array_filter($player->getServer()->getOnlinePlayers(), function ($target) use ($player): bool {
                            return $target instanceof Player && $player->getPosition()->distance($target->getPosition()) <= 20 && $player->getSession()->getFaction() === $target->getSession()->getFaction();
                        });
                       
                        if (count($players) !== 0) {
                            foreach ($players as $target) {
                                $target->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 20 * 7, 2));
                                $target->sendMessage(TextFormat::colorize('&eThe bard in your faction (&a' . $player->getName() . '&e) has used &4Speed III'));
                            }
                        }
                    }
                    $item->pop();
                    $player->getInventory()->setItemInHand($item);
                    $player->getSession()->addCooldown('bard.cooldown', '&l&eBard Effect&r&7: &r&c', 10);
                    $energy->reduceEnergy(20);
                } elseif ($item->getTypeId() === VanillaItems::FEATHER()->getTypeId()) {
                    if ($energy->getEnergy() < 30)
                        return;
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::JUMP_BOOST(), 20 * 7, 7));
                    
                    if ($player->getSession()->getFaction() !== null) {
                        $players = array_filter($player->getServer()->getOnlinePlayers(), function ($target) use ($player): bool {
                            return $target instanceof Player && $player->getPosition()->distance($target->getPosition()) <= 20 && $player->getSession()->getFaction() === $target->getSession()->getFaction();
                        });
                       
                        if (count($players) !== 0) {
                            foreach ($players as $target) {
                                $target->getEffects()->add(new EffectInstance(VanillaEffects::JUMP_BOOST(), 20 * 7, 7));
                                $target->sendMessage(TextFormat::colorize('&eThe bard in your faction (&a' . $player->getName() . '&e) has used &4Jump Boost VIII'));
                            }
                        }
                    }
                    $item->pop();
                    $player->getInventory()->setItemInHand($item);
                    $player->getSession()->addCooldown('bard.cooldown', '&l&eBard Effect&r&7: &r&c', 10);
                    $energy->reduceEnergy(30);
                } elseif ($item->getTypeId() === VanillaItems::GHAST_TEAR()->getTypeId()) {
                    if ($energy->getEnergy() < 35)
                        return;
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 20 * 7, 2));
                    
                    if ($player->getSession()->getFaction() !== null) {
                        $players = array_filter($player->getServer()->getOnlinePlayers(), function ($target) use ($player): bool {
                            return $target instanceof Player && $player->getPosition()->distance($target->getPosition()) <= 20 && $player->getSession()->getFaction() === $target->getSession()->getFaction();
                        });
                       
                        if (count($players) !== 0) {
                            foreach ($players as $target) {
                                $target->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 20 * 7, 2));
                                $target->sendMessage(TextFormat::colorize('&eThe bard in your faction (&a' . $player->getName() . '&e) has used &4Regeneration III'));
                            }
                        }
                    }
                    $item->pop();
                    $player->getInventory()->setItemInHand($item);
                    $player->getSession()->addCooldown('bard.cooldown', '&l&eBard Effect&r&7: &r&c', 10);
                    $energy->reduceEnergy(35);
                }
            }
        }
    }
}