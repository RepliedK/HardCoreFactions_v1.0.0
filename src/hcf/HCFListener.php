<?php

declare(strict_types=1);

namespace hcf;

use CortexPE\DiscordWebhookAPI\Message;
use CortexPE\DiscordWebhookAPI\Webhook;
use hcf\entity\default\EnderpearlEntity;
use hcf\entity\default\SplashPotionEntity;
use hcf\player\Player;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Armor;
use pocketmine\item\Bucket;
use pocketmine\item\EnderPearl;
use pocketmine\item\FlintSteel;
use pocketmine\item\Hoe;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\PotionType;
use pocketmine\item\Shovel;
use pocketmine\item\SplashPotion;
use pocketmine\item\Tool;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\ThrowSound;

class HCFListener implements Listener
{

    /**
     * @param EntityDamageEvent $event
     * @priority HIGH
     */
    public function handleDamage(EntityDamageEvent $event): void
    {
        $cause = $event->getCause();
        $entity = $event->getEntity();

        if ($entity instanceof Player) {
            if ($event->isCancelled()) return;

            if ($entity->getSession()->getCooldown('starting.timer')) {
                $event->cancel();
                return;
            }

            if ($entity->getSession()->getCooldown('pvp.timer') !== null) {
                if ($cause === EntityDamageEvent::CAUSE_ENTITY_ATTACK || $cause === EntityDamageEvent::CAUSE_PROJECTILE) {
                    $event->cancel();
                    return;
                }
            }

            if ($entity->getCurrentClaim() === 'Spawn') {
                $event->cancel();
                return;
            }

            if ($event instanceof EntityDamageByEntityEvent || $event instanceof EntityDamageByChildEntityEvent) {
                $damager = $event->getDamager();

                if ($damager instanceof Player) {
                    if ($damager->getSession()->getCooldown('starting.timer') !== null || $damager->getSession()->getCooldown('pvp.timer') !== null) {
                        $event->cancel();
                        return;
                    }

                    if ($damager->getCurrentClaim() === 'Spawn') {
                        $event->cancel();
                        return;
                    }

                    if ($entity->getSession()->getFaction() !== null && $damager->getSession()->getFaction() !== null) {
                        if ($entity->getSession()->getFaction() === $damager->getSession()->getFaction()) {
                            $damager->sendMessage(TextFormat::colorize('&eYou cannot hurt &2' . $entity->getName() . '§e.'));
                            $event->cancel();
                            return;
                        }
                    }
                    $entity->getSession()->addCooldown('spawn.tag', '&l&cSpawn Tag&r&7: &r&c', 25);
                    $damager->getSession()->addCooldown('spawn.tag', '&l&cSpawn Tag&r&7: &r&c', 30);
                }
            }
        }
    }

    /**
     * @param PlayerChatEvent $event
     */
    public function handleChat(PlayerChatEvent $event): void
    {
        $player = $event->getPlayer();
        $message = $event->getMessage();

        if ($player instanceof Player) {
            if ($player->getSession()->getFaction() !== null && $player->getSession()->hasFactionChat()) {
                $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($player->getSession()->getFaction());

                if ($faction !== null) {
                    $event->cancel();

                    foreach ($faction->getOnlineMembers() as $member)
                        $member->sendMessage(TextFormat::colorize('&a[Faction] ' . $player->getName() . ': ' . $message));
                    return;
                }
            }
        }
    }

    /**
     * @param PlayerCreationEvent $event
     */
    public function handleCreation(PlayerCreationEvent $event): void
    {
        $event->setPlayerClass(Player::class);
    }

    /**
     * @param PlayerDeathEvent $event
     */
    public function handleDeath(PlayerDeathEvent $event): void
    {
        /** @var Player */
        $player = $event->getPlayer();

        if (!$player instanceof Player)
            return;
        $last = $player->getLastDamageCause();

        $killerXuid = null;
        $killer = null;
        $itemInHand = null;
        $message = '';

        if ($last instanceof EntityDamageByEntityEvent || $last instanceof EntityDamageByChildEntityEvent) {
            $damager = $last->getDamager();

            if ($damager instanceof Player) {

                $killerXuid = $damager->getXuid();
                $killer = $damager->getName();
                $itemInHand = $damager->getInventory()->getItemInHand();

                $damager->getSession()->addKill();
                $damager->getSession()->addKillStreak();

                if ($damager->getSession()->getKillStreak() > $damager->getSession()->getHighestKillStreak())
                    $damager->getSession()->addHighestKillStreak();

                if ($damager->getSession()->getFaction() !== null) {
                    $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($damager->getSession()->getFaction());
                    $faction->setPoints($faction->getPoints() + 1);
                }
            }
        }

        if ($player->getSession()->getCooldown('spawn.tag') !== null)
            $player->getSession()->removeCooldown('spawn.tag');
        $spawnClaim = HCFLoader::getInstance()->getClaimManager()->getClaim('Spawn');

        if ($spawnClaim !== null && $spawnClaim->getType() === 'spawn')
            $player->setCurrentClaim($spawnClaim->getName());
        $player->getSession()->addDeath();
        $player->getSession()->setKillStreak(0);
        $player->getSession()->addCooldown('starting.timer', '&l&aPvP Timer&r&7: &r&c', 60 * 60);


        $player->getSession()->addDeath();
        if ($player->getSession()->getFaction() !== null) {
            $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($player->getSession()->getFaction());

            $faction->setPoints($faction->getPoints() - 1);
            $faction->setDtr($faction->getDtr() - 1.0);
            $faction->announce(TextFormat::colorize('&cMember Death: &f' . $player->getName() . "\n" . '&cDTR: &f' . $faction->getDtr()));

            # Faction Raid
            if ($faction->getDtr() < 0.00 && !$faction->isRaidable()) {
                $faction->setRaidable(true);
                $faction->setPoints($faction->getPoints() - 3);

                if ($killerXuid !== null) {
                    $session = HCFLoader::getInstance()->getSessionManager()->getSession($killerXuid);

                    if ($session !== null && $session->getFaction()) {
                        $fac = HCFLoader::getInstance()->getFactionManager()->getFaction($session->getFaction());

                        if ($fac !== null) {
                            $fac->setPoints($fac->getPoints() + 3);
                            $fac->announce(TextFormat::colorize('&cThe faction &l' . $faction->getName() . ' &r&cis now Rideable!'));
                        }
                    }
                }
            }



            # Regen time
            if (!$faction->isRaidable()) {
                $faction->setTimeRegeneration(35 * 60);
            } else {
                $regenTime = $faction->getTimeRegeneration();
                $value = $regenTime + (5 * 60);

                $faction->setTimeRegeneration($value < 35 * 60 ? $value : 35 * 60);
            }

            # Setup scoretag for team members
            foreach ($faction->getOnlineMembers() as $member)
                $member->setScoreTag(TextFormat::colorize('&6[&c' . $faction->getName() . ' &c' . round($faction->getDtr(), 2) . '■&6]'));
        }



        if ($killer === null) {
            $message = '&c' . $player->getName() . '&4[' . $player->getSession()->getKills() . '] &edied';
            $webhook = $player->getName() . '[' . $player->getSession()->getKills() . '] died';
        } else {
            if (!$itemInHand->isNull() && $itemInHand instanceof Tool) {
                $message = '&c' . $player->getName() . '&4[' . $player->getSession()->getKills() . '] &ewas slain by &c' . $killer . '&4[' . HCFLoader::getInstance()->getSessionManager()->getSession($killerXuid)->getKills() . '] &cusing ' . $itemInHand->getName();
            } else {
                $message = '&c' . $player->getName() . '&4[' . $player->getSession()->getKills() . '] &ewas slain by &c' . $killer . '&4[' . HCFLoader::getInstance()->getSessionManager()->getSession($killerXuid)->getKills() . ']';
            }
            $webhook = '`' . $player->getName() . '[' . $player->getSession()->getKills() . '] was slain by ' . $killer . '[' . HCFLoader::getInstance()->getSessionManager()->getSession($killerXuid)->getKills() . ']`';
        }
        # Construct a discord webhook with its URL
        $webHook = new Webhook(HCFLoader::getInstance()->getConfig()->get('kills.webhook'));

        # Construct a new Message object
        $msg = new Message();
        $msg->setContent($webhook);
        $webHook->send($msg);

        $event->setDeathMessage(TextFormat::colorize($message));
    }

    /**
     * @param PlayerExhaustEvent $event
     */
    public function handleExhaust(PlayerExhaustEvent $event): void
    {
        $player = $event->getPlayer();

        if ($player instanceof Player) {
            if ($player->getCurrentClaim() !== null) {
                $claim = HCFLoader::getInstance()->getClaimManager()->getClaim($player->getCurrentClaim());

                if ($claim !== null && $claim->getType() === 'spawn') {
                    $event->cancel();

                    if ($player->getHungerManager()->getFood() !== $player->getHungerManager()->getMaxFood())
                        $player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
                    return;
                }
            }

            if ($player->getSession()->hasAutoFeed()) {
                $event->cancel();

                if ($player->getHungerManager()->getFood() !== $player->getHungerManager()->getMaxFood())
                    $player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
                return;
            }
        }
    }

    /**
     * @param PlayerInteractEvent $event
     * @priority HIGHEST
     */
    public function handleInteract(PlayerInteractEvent $event)
    {
        $action = $event->getAction();
        $block = $event->getBlock();
        $player = $event->getPlayer();
        $item = $event->getItem();

        if (!$player instanceof Player)
            return;

        if ($player->getPosition()->distance($player->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn()->asVector3()) < 170) {
            if ($item instanceof Bucket) {
                $event->cancel();
                return;
            }

            if ($item instanceof Shovel) {
                $event->cancel();
                return;
            }

            if ($item instanceof Hoe) {
                $event->cancel();
                return;
            }
        }

        if ($item instanceof FlintSteel) {
            $event->cancel();
            return;
        }
        
    }

    /**
     * @param PlayerItemConsumeEvent $event
     */
    public function handleItemConsume(PlayerItemConsumeEvent $event): void
    {
        /** @var Player */
        $player = $event->getPlayer();
        $item = $event->getItem();

        if ($player instanceof Player)

            if ($event->isCancelled())
                return;

        if ($item->getTypeId() == ItemTypeIds::GOLDEN_APPLE) {
            if ($player->getSession()->getCooldown('apple') !== null) {
                $event->cancel();
                return;
            }
            $player->getSession()->addCooldown('apple', '&l&eApple: &r&7', 15);
        } elseif ($item->getTypeId() == ItemTypeIds::ENCHANTED_GOLDEN_APPLE) {
            if ($player->getSession()->getCooldown('apple.enchanted') !== null) {
                $event->cancel();
                return;
            }
            $player->getSession()->addCooldown('apple.enchanted', '&l&6Gapple&r&7: &r&c', 3600);
        }
    }

    /**
     * @param PlayerItemUseEvent $event
     */
    public function handleItemUse(PlayerItemUseEvent $event): void
    {
        $item = $event->getItem();

        if ($item instanceof Armor) {
            $event->cancel();
            return;
        }
    }

    /**
     * @param PlayerJoinEvent $event
     */
    public function handleJoin(PlayerJoinEvent $event): void
    {
        /** @var Player */
        $player = $event->getPlayer();
        $player->join();

        $joinMessage = str_replace('{player}', $player->getName(), HCFLoader::getInstance()->getConfig()->get('join.message'));
        $event->setJoinMessage(TextFormat::colorize($joinMessage));
    }

    /**
     * @param PlayerLoginEvent $event
     */
    public function handleLogin(PlayerLoginEvent $event): void
    {
        $player = $event->getPlayer();
        $session = HCFLoader::getInstance()->getSessionManager()->getSession($player->getXuid());

        if ($session === null)
            HCFLoader::getInstance()->getSessionManager()->addSession($player->getXuid(), [
                'name' => $player->getName(),
                'faction' => null,
                'balance' => 0,
                'crystals' => 0,
                'cooldowns' => [],
                'energies' => [],
                'stats' => [
                    'kills' => 0,
                    'deaths' => 0,
                    'killStreak' => 0,
                    'highestKillStreak' => 0
                ]
            ]);
        else {
            if ($player->getName() !== $session->getName())
                $session->setName($player->getName());
        }
    }

    /**
     * @param PlayerQuitEvent $event
     */
    public function handleQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();

        if (!$player instanceof Player)
            return;
        $quitMessage = str_replace('{player}', $player->getName(), HCFLoader::getInstance()->getConfig()->get('quit.message'));
        $disconnectedManager = HCFLoader::getInstance()->getDisconnectedManager();

        if ($player->getSession() !== null && $player->getSession()->getFaction() !== null) {
            $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($player->getSession()->getFaction());
            $faction->announce(TextFormat::colorize("&cMember offline: &f" . $player->getName() . "\n&cDTR: &f" . $faction->getDtr()));
        }

        if ($player->getSession() !== null && !$player->getSession()->isLogout()) {
            if ($player->getCurrentClaim() !== null) {
                $claim = HCFLoader::getInstance()->getClaimManager()->getClaim($player->getCurrentClaim());
                $disconnectedManager->addDisconnected($player);
            }
        }
        $event->setQuitMessage(TextFormat::colorize($quitMessage));
    }

    public function handlePlayerItemUse(PlayerItemUseEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $event->getItem();
        if(!$player instanceof Player) return;
        if($item instanceof SplashPotion){
            $location = $player->getLocation();

            $projectile = new SplashPotionEntity(Location::fromObject($player->getEyePos(), $player->getWorld(), $location->yaw, $location->pitch), $player, PotionType::STRONG_HEALING());
            $projectile->setMotion($player->getDirectionVector()->multiply($item->getThrowForce()));

            $projectileEv = new ProjectileLaunchEvent($projectile);
            $projectileEv->call();
            if($projectileEv->isCancelled()){
                $projectile->flagForDespawn();
                return;
            }
            $projectile->spawnToAll();
            $item->pop();
            $player->getInventory()->setItemInHand($item);
            $event->cancel();
        }
        if($item instanceof EnderPearl){
            $session = $player->getSession();
            if ($session->getCooldown('enderpearl') !== null) {
                $player->sendMessage(TextFormat::colorize('&cYou have cooldown enderpearl'));
                $event->cancel();
                return;
            }
            $location = $player->getLocation();
            $projectile = new EnderpearlEntity(Location::fromObject($player->getEyePos(), $player->getWorld(), $location->yaw, $location->pitch), $player);
            $projectile->setMotion($player->getDirectionVector()->multiply($item->getThrowForce() * 1.4));
            $projectileEv = new ProjectileLaunchEvent($projectile);
            $projectileEv->call();
            if($projectileEv->isCancelled()){
                $projectile->flagForDespawn();
                return;
            }

            $projectile->spawnToAll();

            $location->getWorld()->addSound($location, new ThrowSound());
            $session->addCooldown('enderpearl', '&l&eEnderpearl&r&7: &r&c', 15);
            $item->pop();
            $player->getInventory()->setItemInHand($item);
            $event->cancel();
            return;
        }
    }

}
