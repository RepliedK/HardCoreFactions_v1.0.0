<?php

declare(strict_types=1);

namespace hcf\player;

use hcf\module\enchantment\Enchantment;
use hcf\HCFLoader;
use hcf\handler\kit\classes\ClassFactory;
use hcf\handler\kit\classes\HCFClass;
use hcf\session\Session;
use hcf\timer\types\TimerCustom;
use hcf\utils\logic\time\Timer;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Location;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\player\Player as BasePlayer;
use pocketmine\player\PlayerInfo;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\network\mcpe\protocol\types\BoolGameRule;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;

/**
 * Class Player
 * @package hcf\player
 */
class Player extends BasePlayer
{
    
    /** @var int|float  */
    private int|float $lastCheck = -1;

    /** @var int */
    private int $lastLine = 0;

    /** @var PlayerScoreboard */
    private PlayerScoreboard $scoreboard;

    private ?HCFClass $class = null;

    /** @var string|null */
    private ?string $currentClaim = null;
    
    /** @var bool */
    private bool $god = false;

    /**
     * Player construct.
     * @param Server $server
     * @param NetworkSession $session
     * @param PlayerInfo $thisInfo
     * @param bool $authenticated
     * @param Location $spawnLocation
     * @param CompoundTag|null $namedtag
     */
    public function __construct(Server $server, NetworkSession $session, PlayerInfo $thisInfo, bool $authenticated, Location $spawnLocation, ?CompoundTag $namedtag)
    {
        parent::__construct($server, $session, $thisInfo, $authenticated, $spawnLocation, $namedtag);
        $this->scoreboard = new PlayerScoreboard($this);
    }
    
    /**
     * @return PlayerScoreboard
     */
    public function getScoreboard(): PlayerScoreboard
    {
        return $this->scoreboard;
    }

    public function getClass(): ?HCFClass
    {
        return $this->class;
    }

    public function setClass(?HCFClass $class): void
    {
        $this->class = $class;
    }
    
    /**
     * @return string|null
     */
    public function getCurrentClaim(): ?string
    {
        return $this->currentClaim;
    }
    
    /**
     * @return bool
     */
    public function isGod(): bool
    {
        return $this->god;
    }
    
    /**
     * @return Session|null
     */
    public function getSession(): ?Session
    {
        return HCFLoader::getInstance()->getSessionManager()->getSession($this->getXuid());
    }
    
    /**
     * @param string|null $claimName
     */
    public function setCurrentClaim(?string $claimName = null): void
    {
        $this->currentClaim = $claimName;
    }
    
    /**
     * @param bool $value
     */
    public function setGod(bool $value): void
    {
        $this->god = $value;
    }
    
    public function join(): void
    {
        # Scoreboard setup
        $this->scoreboard->init();
        
        # Scoretag & Nametag setup
        $this->setNameTag(TextFormat::colorize('&c' . $this->getName()));
        
        if ($this->getSession()->getFaction() !== null) {
            $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($this->getSession()->getFaction());
            $faction->announce(TextFormat::colorize('&aMember online: &f' . $this->getName()));

            $this->setScoreTag(TextFormat::colorize('&6[&c' . $faction->getName() . ' ' . ($faction->getDtr() === (count($faction->getMembers()) + 0.1) ? '&a' : '&c') . $faction->getDtr() . '■&6]'));
        }
        
        # Disconnected 
        $disconnectedManager = HCFLoader::getInstance()->getDisconnectedManager();
        $disconnected = $disconnectedManager->getDisconnected($this->getXuid());
        
        if ($disconnected !== null)
            $disconnected->join($this);
        
        # Add coordinates
        $pk = GameRulesChangedPacket::create([
            'showCoordinates' => new BoolGameRule(true, false)
        ]);
        $this->getNetworkSession()->sendDataPacket($pk);
        
        # Mob
        if ($this->getSession()->isMobKilled()) {
            $this->getSession()->setMobKilled(false);
            $this->getInventory()->clearAll();
            $this->getArmorInventory()->clearAll();
            $this->getEffects()->clear();
            $this->setHealth($this->getMaxHealth());
            $this->teleport($this->getWorld()->getSafeSpawn());
        }
        
        # Logout
        if ($this->getSession()->isLogout())
            $this->getSession()->setLogout(false);
    }
    
    private function updateScoreboard(): void
    {
        $lines = [
            TextFormat::colorize('&7'.HCFLoader::getInstance()->getConfig()->get('scoreboard.placeholder'))
        ];

        # Claims
        if ($this->getCurrentClaim() !== null) {
            $currentClaim = HCFLoader::getInstance()->getClaimManager()->getClaim($this->getCurrentClaim());
            if ($currentClaim === null) {
                $lines[] = TextFormat::colorize(' &l&4Claim&r&7: &c'. ($this->getPosition()->distance($this->getWorld()->getSafeSpawn()) > 400 ? 'Wilderness' : 'Warzone'));
                return;
            }
            $claimName = $this->getCurrentClaim();
            $colorClaimMatch = match($currentClaim->getType()){
                "spawn" => "&a{$claimName}",
                "road" => "&e{$claimName}",
                "koth" => "&9KoTH {$claimName}",
                default => "&c{$claimName}",
            };
            $lines[] = TextFormat::colorize(' &l&4Claim&r&7: '. $colorClaimMatch);
        }
        
        # Events
        $timerManager = HCFLoader::getInstance()->getTimerManager();
        if (($sotw = $timerManager->getSotw())->isActive()) {
            $lines[] = TextFormat::colorize(' ' . $sotw->getFormat() . Timer::format($sotw->getTime()));
        }
        if (($eotw = $timerManager->getEotw())->isActive()) {
            $lines[] = TextFormat::colorize(' ' . $eotw->getFormat() . Timer::format($eotw->getTime()));
        }
        if (($purge = $timerManager->getPurge())->isActive()) {
            $lines[] = TextFormat::colorize(' ' . $purge->getFormat() . Timer::format($purge->getTime()));
        }
        foreach ($timerManager->getCustomTimers() as $name => $timer) {
            if (!($timer instanceof TimerCustom)) {
                continue;
            }
            if (($custom = $timerManager->getCustomTimerByName($name))->isActive()) {
                $lines[] = TextFormat::colorize(' ' . $custom->getFormat() . Timer::format($custom->getTime()));
            }
        }
        # Koth
        if (($kothName = HCFLoader::getInstance()->getKothManager()->getKothActive()) !== null) {
            $koth = HCFLoader::getInstance()->getKothManager()->getKoth($kothName);
            
            if ($koth !== null) $lines[] = TextFormat::colorize('&9&l ' . $koth->getName() . '&r&7: &r&c' . Timer::format($koth->getProgress()));
        }
        
        # Cooldowns
        foreach ($this->getSession()->getCooldowns() as $cooldown) {
            if ($cooldown->isVisible())
                $lines[] = TextFormat::colorize(' ' . $cooldown->getFormat() . Timer::format($cooldown->getTime()));
        }

        foreach ($this->getSession()->getEnergies() as $energy) {
            $lines[] = TextFormat::colorize(' ' . $energy->getFormat() . ($energy->getEnergy().'.0'));
        }
        
        # Faction
        if ($this->getSession()->getFaction() !== null) {
            $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($this->getSession()->getFaction());
            
            # Focus
            if ($faction->getFocus() !== null) {
                if (($targetFaction = HCFLoader::getInstance()->getFactionManager()->getFaction($faction->getFocus())) !== null) {
                    $lines[] = TextFormat::colorize('&r&7'.HCFLoader::getInstance()->getConfig()->get('scoreboard.placeholder'));
                    $lines[] = TextFormat::colorize(' &l&dTeam&r&7: &e' . $targetFaction->getName());
                    $lines[] = TextFormat::colorize(' &l&dHQ&r&7: &e' . ($targetFaction->getHome() !== null ? $targetFaction->getHome()->getFloorX() . ', ' . $targetFaction->getHome()->getFloorZ() : 'Has no home'));
                    $lines[] = TextFormat::colorize(' &l&dDTR&r&7: &e' . $targetFaction->getDtr() . ' &c■');
                    $lines[] = TextFormat::colorize(' &l&dOnline&r&7: &e' . count($targetFaction->getOnlineMembers()));
                }
            }
            
            # Rally
            if (($rally = $faction->getRally()) !== null) {
                $lines[] = TextFormat::colorize('&r&7'.HCFLoader::getInstance()->getConfig()->get('scoreboard.placeholder'));
                $lines[] = TextFormat::colorize(' &l&dRally&r&7: &e' . $rally[0]);
                $lines[] = TextFormat::colorize(' &l&dXYZ&r&7: &e' . $rally[1]->getPosition()->getFloorX() . ', ' . $rally[1]->getPosition()->getFloorY() . ', ' . $rally[1]->getPosition()->getFloorZ());
            }
        }
        $lines[] = TextFormat::colorize('&7&7'.HCFLoader::getInstance()->getConfig()->get('scoreboard.placeholder'));

        if (count($lines) === 3) {
            if ($this->scoreboard->isSpawned()) $this->scoreboard->remove();
            return;
        }

        if (!$this->scoreboard->isSpawned())
            $this->scoreboard->init();
        else $this->scoreboard->clear();
        
        foreach ($lines as $line => $content)
            $this->scoreboard->addLine($content.' ');
    }

    /**
     * @param int $currentTick
     */
    public function onUpdate(int $currentTick): bool
    {
        $update = parent::onUpdate($currentTick);
        
        if ($update) {
            if ($currentTick % 20 === 0) {

                # Update custom enchants
                foreach ($this->getArmorInventory()->getContents() as $armor) {
                    foreach ($armor->getEnchantments() as $enchantment) {
                        $type = $enchantment->getType();

                        if ($type instanceof Enchantment)
                            $type->giveEffect($this);
                    }
                }
                
                # Update scoreboard
                $this->updateScoreboard();
                
                # Update invisibility 
                $this->loadInvisibility();

                if ($this->getClass() !== null)
                    $this->getClass()->onRun($this);
                else {
                    foreach(ClassFactory::getClasses() as $class) {
                        if ($class->isActive($this)) {
                            $this->class = $class;
                            break;
                        }
                    }
                }
            }
            
            if ($currentTick % 40 === 0) {
                
                # Update last line
                if ($this->lastLine >= 2) {
                    $this->lastLine = 0;
                } else {
                    $this->lastLine++;
                }
            }
        }
        return $update;
    }

    public function loadInvisibility() : void
    {
        if (!$this->getEffects()->has(VanillaEffects::INVISIBILITY()))
            return;
        $metadata = clone $this->getNetworkProperties();
        $metadata->setGenericFlag(EntityMetadataFlags::INVISIBLE, false);
        $pk2 = new SetActorDataPacket();
        $pk2->syncedProperties = new PropertySyncData([], []);
        $pk2->actorRuntimeId = $this->getId();
        $pk2->metadata = $metadata->getAll();

        foreach ($this->getViewers() as $viewer) {
            if ($viewer instanceof self) {
                if ($viewer->getSession()->getFaction() === null)
                    continue;

                if ($viewer->getSession()->getFaction() === $this->getSession()->getFaction())
                    $viewer->getNetworkSession()->sendDataPacket($pk2);
            }
        }
    }
    
    protected function processMostRecentMovements() : void
    {
        $micro = microtime(true);
        
        if ($micro - $this->lastCheck > 1) {
            $this->lastCheck = $micro;
            
            foreach ($this->getArmorInventory()->getContents() as $armor) {
                foreach ($armor->getEnchantments() as $enchantment) {
                    $type = $enchantment->getType();

                    if ($type instanceof Enchantment)
                        $type->handleMove($this);
                }
            }
        }
        parent::processMostRecentMovements();
    }
    
    /**
     * @return string
     */
    public function getViewPos(): string
    {
        $deg = $this->getLocation()->getYaw() - 90;
        $deg %= 360;
        if ($deg < 0)
            $deg += 360;

        if (22.5 <= $deg and $deg < 157.5)
            return "N";
        elseif (157.5 <= $deg and $deg < 202.5)
            return "E";
        elseif (202.5 <= $deg and $deg < 337.5)
            return "S";
        else
            return "W";
    }
}
