<?php

declare(strict_types=1);

namespace hcf\claim;

use hcf\entity\EnderpearlEntity;
use hcf\HCFLoader;
use hcf\player\Player;

use pocketmine\block\FenceGate;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\tile\Sign;
use pocketmine\entity\Location;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\EnderPearl;
use pocketmine\math\Vector3;
use pocketmine\utils\TextFormat;
use pocketmine\world\WorldException;

/**
 * Class ClaimListener
 * @package hcf\claim
 */
class ClaimListener implements Listener
{

    /** @var string */
    const DEATHBAN = '&7[&cDeathban&7]';
    /** @var string */
    const NO_DEATHBAN = '&7[&aNon-Deathban&7]';

    public function handleChat(PlayerChatEvent $event): void
    {
        /** @var Player $player */
        $player = $event->getPlayer();
        $message = $event->getMessage();
        if($message == "accept"){
            if (($creator = HCFLoader::getInstance()->getClaimManager()->getCreator($player->getName())) !== null) {
                if (!$creator->isValid()) return;
                if($creator->getType() === 'faction'){
                    $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($player->getSession()->getFaction());
                    $balance = $faction->getBalance() - $creator->calculateValue();
                    if ($balance < 0) {
                        $player->sendMessage(TextFormat::colorize('&cYour faction does not have enough money to pay the claim'));
                        return;
                    }
                    $faction->setBalance($balance);
                }
                $creator->deleteCorners($player);
                HCFLoader::getInstance()->getClaimManager()->createClaim($creator->getName(), $creator->getType(), $creator->getMinX(), $creator->getMaxX(), $creator->getMinZ(), $creator->getMaxZ(), $creator->getWorld());
                $player->sendMessage(TextFormat::colorize('&aYou have made the claim of the claim ' . $creator->getName()));
                HCFLoader::getInstance()->getClaimManager()->removeCreator($player->getName());
            
                foreach ($player->getInventory()->getContents() as $slot => $i) {
                    if ($i->getNamedTag()->getTag('claim_type')) {
                        $player->getInventory()->clear($slot);
                        break;
                    }
                }
            }
            return;
            $event->cancel();
        }
        if($message == "cancel"){
            if (($creator = HCFLoader::getInstance()->getClaimManager()->getCreator($player->getName())) !== null) {
                $creator->deleteCorners($player);
                HCFLoader::getInstance()->getClaimManager()->removeCreator($player->getName());
                $player->sendMessage(TextFormat::colorize('&cYou have canceled the claim'));
            } else
                $player->sendMessage(TextFormat::colorize('&cYou are not in claim mode yet'));
            return;
            $event->cancel();
        }
    }

    /**
     * @param BlockBreakEvent $event
     * @throws WorldException
     */
    public function handleBreak(BlockBreakEvent $event): void
    {
        /** @var Player $player */
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $claim = HCFLoader::getInstance()->getClaimManager()->insideClaim($block->getPosition());

        if ($event->isCancelled())
            return;

        if ($player->isGod())
            return;

        if ($claim === null) {
            if ($block->getPosition()->distance($player->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn()->asVector3()) < 400)
                $event->cancel();
            return;
        }

        if (in_array($claim->getType(), ['spawn', 'road', 'koth', 'citadel', 'custom'])) {
            $event->cancel();
            $player->sendMessage(TextFormat::colorize('&cYou cannot place blocks in this area'));
            return;
        }

        if (!HCFLoader::getInstance()->getTimerManager()->getEotw()->isActive() && $player->getSession()->getFaction() !== $claim->getName()) {
            $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($claim->getName());

            if ($faction !== null && $faction->getDtr() > 0.00) {
                $event->cancel();
                $player->sendMessage(TextFormat::colorize('&cYou cannot place blocks in ' . $claim->getName() . ' territory'));
            }
        }
    }

    /**
     * @param BlockPlaceEvent $event
     * @throws WorldException
     */
    public function handlePlace(BlockPlaceEvent $event): void
    {
        /** @var Player $player */
        $player = $event->getPlayer();
        $block = $event->getBlockAgainst();
        $claim = HCFLoader::getInstance()->getClaimManager()->insideClaim($block->getPosition());

        if ($block->getTypeId() === VanillaBlocks::TNT()->getTypeId()){
            $event->cancel();
        }

        if ($event->isCancelled())
            return;

        if ($player->isGod())
            return;

        if ($claim === null) {
            if ($block->getPosition()->distance($player->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn()->asVector3()) < 400)
                $event->cancel();
            return;
        }

        if (in_array($claim->getType(), ['spawn', 'road', 'koth', 'citadel', 'custom'])) {
            $event->cancel();
            $player->sendMessage(TextFormat::colorize('&cYou cannot place blocks in this area'));
            return;
        }

        if (!HCFLoader::getInstance()->getTimerManager()->getEotw()->isActive() && $player->getSession()->getFaction() !== $claim->getName()) {
            $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($claim->getName());

            if ($faction !== null && $faction->getDtr() > 0.00) {
                $event->cancel();
                $player->sendMessage(TextFormat::colorize('&cYou cannot place blocks in ' . $claim->getName() . ' territory'));
            }
        }
    }

    /**
     * @param EntityTeleportEvent $event
     */
    public function handleTeleport(EntityTeleportEvent $event): void
    {
        $entity = $event->getEntity();
        $to = $event->getTo();

        if (!$entity instanceof Player)
            return;
        $claim = HCFLoader::getInstance()->getClaimManager()->insideClaim($to);

        if ($claim === null)
            return;

        if ($entity->getSession()->getCooldown('spawn.tag') !== null) {
            if ($claim->getType() == 'spawn') {
                $event->cancel();
                $entity->sendMessage(TextFormat::colorize('&cYou have Spawn Tag. You cannot teleport to this location'));
                return;
            }
        } elseif ($entity->getSession()->getCooldown('pvp.timer') !== null) {
            if ($claim->getType() === 'faction' && $entity->getSession()->getFaction() !== $claim->getName()) {
                $event->cancel();
                $entity->sendMessage(TextFormat::colorize('&cYou have PvP Timer. You cannot teleport to this location'));
                return;
            }
        }
        $entity->setCurrentClaim($claim->getName());
    }

    /**
     * @param PlayerDropItemEvent $event
     */
    public function handleDropItem(PlayerDropItemEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $event->getItem();

        if (HCFLoader::getInstance()->getClaimManager()->getCreator($player->getName()) !== null) {
            if ($item->getNamedTag()->getTag('claim_type'))
                $event->cancel();
        }
    }

    /**
     * @param PlayerInteractEvent $event
     */
    public function handleInteract(PlayerInteractEvent $event): void
    {
        $action = $event->getAction();
        $block = $event->getBlock();
        /** @var Player $player */
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();

        if (($creator = HCFLoader::getInstance()->getClaimManager()->getCreator($player->getName())) !== null) {
            if ($item->getNamedTag()->getTag('claim_type') !== null) {
                $event->cancel();

                if (($claim = HCFLoader::getInstance()->getClaimManager()->insideClaim($block->getPosition())) !== null && ($claim->getType() !== 'koth' || $claim->getName() !== $creator->getName())) {
                    $player->sendMessage(TextFormat::colorize('&cYou cannot make a claim in an area that is already claiming'));
                    return;
                }

                if ($creator->getType() === 'faction') {
                    if ($block->getPosition()->distance($player->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn()->asVector3()) < 400) {
                        $player->sendMessage(TextFormat::colorize('&cYou can\'t claim in this position'));
                        return;
                    }
                }

                if ($action === PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
                    if ($creator->getFirst() === null) {
                        $creator->calculate($block->getPosition(), $player);
                        $player->sendMessage(TextFormat::colorize('&aYou have selected the first position. Now select the second position'));
                    } else {
                        $result = $creator->calculate($block->getPosition(), $player, false);

                        if (!$result) {
                            $player->sendMessage(TextFormat::colorize('&cERROR: The position was not selected in the same world'));
                            HCFLoader::getInstance()->getClaimManager()->removeCreator($player->getName());

                            foreach ($player->getInventory()->getContents() as $slot => $i) {
                                if ($i->getNamedTag()->getTag('claim_type')) {
                                    $player->getInventory()->clear($slot);
                                    break;
                                }
                            }
                            return;
                        }

                        if ($creator->calculateClaim($creator->getFirst(), $block->getPosition())) {
                            if ($creator->getType() === 'capzone') {
                                return;
                            }
                            $player->sendMessage(TextFormat::colorize('&cERROR: The position was selected in other faction'));
                            return;
                        }

                        $player->sendMessage(TextFormat::colorize('&aYou have selected the second position.'));

                        if ($creator->getType() === 'faction') {
                            $player->sendMessage(TextFormat::colorize('&aThe price of your claim is $' . $creator->calculateValue() . '. &7(Type again /f claim to accept or /f claim cancel to cancel)'));
                        }
                    }
                }
            }
        }

        if ($item instanceof EnderPearl) {
            if ($action === PlayerInteractEvent::RIGHT_CLICK_BLOCK && $block instanceof FenceGate) {
                $event->cancel();
                $session = $player->getSession();

                if ($player->getCurrentClaim() === 'Citadel') {
                    $player->sendMessage(TextFormat::colorize('&cYou can\'t use this in Citadel &cclaim.'));
                    return;
                }
            }
        }

        if ($player->isGod())
            return;
        $claim = HCFLoader::getInstance()->getClaimManager()->insideClaim($block->getPosition());

        if ($claim === null)
            return;
            
        if (!$block instanceof Sign) {
            if (!HCFLoader::getInstance()->getTimerManager()->getEotw()->isActive() && $player->getSession()->getFaction() !== $claim->getName() && $claim->getType() !== 'spawn') {
                $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($claim->getName());

                if ($faction !== null && $faction->getDtr() > 0.00 && !HCFLoader::getInstance()->getTimerManager()->getPurge()->isActive()) {
                    $event->cancel();
                
                    if ($action === PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
                        if ($block instanceof FenceGate) {
                            if(HCFLoader::getInstance()->getTimerManager()->getPurge()->isActive()) return;
                            $distance = $player->getPosition()->distance($block->getPosition());

                            if ($distance <= 3 && !$block->isOpen()) {
                                $player->setMotion($player->getDirectionVector()->multiply(-1.5));
                            }
                        }
                    }
                }
                return;
            }
        }
    }

    /**
     * @param PlayerJoinEvent $event
     */
    public function handleJoin(PlayerJoinEvent $event): void
    {
        /** @var Player $player */
        $player = $event->getPlayer();
        $claim = HCFLoader::getInstance()->getClaimManager()->insideClaim($player->getPosition());

        if ($claim !== null)
            $player->setCurrentClaim($claim->getName());
    }

    /**
     * @param PlayerMoveEvent $event
     * @throws WorldException
     */
    public function handleMove(PlayerMoveEvent $event): void
    {
        /** @var Player $player */
        $player = $event->getPlayer();
        $claim = HCFLoader::getInstance()->getClaimManager()->insideClaim($player->getPosition());

        $leaving = self::DEATHBAN;
        $entering = self::DEATHBAN;

        if ($event->isCancelled())
            return;
        if (!$this->isBorderLimit($player->getPosition())) {
            $player->teleport($this->correctPosition($player->getPosition()));
        }

        if ($claim === null) {
            if ($player->getCurrentClaim() !== null) {
                $currentClaim = HCFLoader::getInstance()->getClaimManager()->getClaim($player->getCurrentClaim());
                $leavingName = '&c' . $player->getCurrentClaim();

                if ($currentClaim !== null) {
                    if ($currentClaim->getType() === 'spawn') {
                        $leaving = self::NO_DEATHBAN;
                        $leavingName = '&a' . $player->getCurrentClaim();

                        if ($player->getSession()->getCooldown('pvp.timer') !== null && $player->getSession()->getCooldown('pvp.timer')->isPaused())
                            $player->getSession()->getCooldown('pvp.timer')->setPaused(false);
                    } elseif ($currentClaim->getType() === 'road') {
                        $leavingName = '&6' . $player->getCurrentClaim();
                    }elseif ($currentClaim->getType() === 'koth'){
                        $leavingName = '&9KoTH ' . $player->getCurrentClaim();
                    }
                }
                $player->sendMessage(TextFormat::colorize('&eNow leaving: ' . $leavingName . ' ' . $leaving));
                $player->sendMessage(TextFormat::colorize('&eNow entering:&c ' . ($player->getPosition()->distance($player->getWorld()->getSafeSpawn()) > 400 ? 'Wilderness' : 'Warzone') . ' ' . $entering));

                $player->setCurrentClaim();
            }
            return;
        }

        if ($player->getCurrentClaim() !== null && $claim->getName() === $player->getCurrentClaim())
            return;

        if ($player->getCurrentClaim() !== null) {
            $currentClaim = HCFLoader::getInstance()->getClaimManager()->getClaim($player->getCurrentClaim());

            if ($currentClaim !== null) {
                $leaving = self::NO_DEATHBAN;
                $leavingName = '&a' . $player->getCurrentClaim();

                if ($currentClaim->getType() === 'spawn') {
                    if ($player->getSession()->getCooldown('pvp.timer') !== null && $player->getSession()->getCooldown('pvp.timer')->isPaused())
                        $player->getSession()->getCooldown('pvp.timer')->setPaused(false);
                } elseif ($currentClaim->getType() === 'road') {
                    $leavingName = '&6' . $player->getCurrentClaim();
                } elseif ($currentClaim->getType() === 'koth'){
                    $leavingName = '&9KoTH ' . $player->getCurrentClaim();
                }
                $player->sendMessage(TextFormat::colorize('&eNow leaving: ' . $leavingName . ' ' . $leaving));
            }
        }
        $enteringName = '&c' . $claim->getName();

        if ($claim->getType() === 'spawn') {
            $entering = self::NO_DEATHBAN;
            $enteringName = '&a' . $claim->getName();

            if ($player->getSession()->getCooldown('spawn.tag') !== null) {
                $event->cancel();
                return;
            }

            if ($player->getSession()->getCooldown('pvp.timer') !== null && !$player->getSession()->getCooldown('pvp.timer')->isPaused())
                $player->getSession()->getCooldown('pvp.timer')->setPaused(true);
        } elseif ($claim->getType() === 'road'){
            $enteringName = '&6' . $claim->getName();
        }elseif ($claim->getType() === 'koth'){
            $enteringName = '&9KoTH ' . $claim->getName();
        }else {
            if ($player->getSession()->getCooldown('pvp.timer') !== null) {
                $event->cancel();
                return;
            }

            if ($player->getSession()->getCooldown('starting.timer') !== null && $player->getSession()->getFaction() !== $claim->getName()) {
                $event->cancel();
                return;
            }
        }
        $player->sendMessage(TextFormat::colorize('&eNow entering: ' . $enteringName . ' ' . $entering));
        $player->sendMessage(TextFormat::colorize('&eNow leaving:&c ' . ($player->getPosition()->distance($player->getWorld()->getSafeSpawn()) > 400 ? 'Wilderness' : 'Warzone') . ' ' . $entering));
        $player->setCurrentClaim($claim->getName());
    }

    protected function isBorderLimit(Vector3 $position): bool
    {
        $border = 1300;
        return $position->getFloorX() >= -$border && $position->getFloorX() <= $border && $position->getFloorZ() >= -$border && $position->getFloorZ() <= $border;
    }

    protected function correctPosition(Vector3 $position): Vector3
    {
        $border = 1300;

        $x = $position->getFloorX();
        $y = $position->getFloorY();
        $z = $position->getFloorZ();

        $xMin = -$border;
        $xMax = $border;

        $zMin = -$border;
        $zMax = $border;

        if ($x <= $xMin) {
            $x = $xMin + 4;
        } elseif ($x >= $xMax) {
            $x = $xMax - 4;
        }
        if ($z <= $zMin) {
            $z = $zMin + 4;
        } elseif ($z >= $zMax) {
            $z = $zMax - 4;
        }
        $y = 72;
        return new Vector3($x, $y, $z);
    }

    /**
     * @param PlayerQuitEvent $event
     */
    public function handleQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();

        if (HCFLoader::getInstance()->getClaimManager()->getCreator($player->getName()) !== null) {
            HCFLoader::getInstance()->getClaimManager()->removeCreator($player->getName());

            foreach ($player->getInventory()->getContents() as $slot => $i) {
                if ($i->getNamedTag()->getTag('claim_type')) {
                    $player->getInventory()->clear($slot);
                    break;
                }
            }
        }
    }
}
