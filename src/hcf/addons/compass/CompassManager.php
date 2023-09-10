<?php

namespace hcf\addons\compass;

use hcf\HCFLoader;
use hcf\utils\bossbar\BossBar;
use hcf\utils\bossbar\BossBarTracker;
use hcf\utils\Utils;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class CompassManager implements Listener
{
    /** @var BossBar[] */
    public array $bossbars = [];
    /** @var string[] */
    public $bars;

    public function __construct()
    {
        BossBarTracker::init(HCFLoader::getInstance());
        HCFLoader::getInstance()->getServer()->getPluginManager()->registerEvents($this, HCFLoader::getInstance());
        $this->bars = new \SplFixedArray(360 + 1);

        $bar = [];
        foreach([
                    "&8|&r&eN&r&8|&r",
                    "&8|&r&eNE&r&8|&r",
                    "&8|&r&aE&r&8|&r",
                    "&8|&r&eSE&r&8|&r",
                    "&8|&r&cS&r&8|&r",
                    "&8|&r&eNW&r&8|&r",
                    "&8|&r&bW&r&8|&r",
                    "&8|&r&eNW&r&8|&r",
                ] as $dir) {
            $bar[] = TextFormat::colorize($dir);
            for($i = 0; $i < 44; $i++) {
                $bar[] = TextFormat::colorize("&7|&r");
            }
        }
        for($yaw = 0; $yaw <= 360; $yaw++) {
            $this->bars[$yaw] = implode("", array_slice(Utils::array_shift_circular($bar, // offset it a bit for accuracy... 1/2 of display width seems to do the job well
                (int) (-($yaw - (75 / 2)))), 0, 75));
        }
        
    }

    /**
     * @param PlayerMoveEvent $ev
     *
     * @priority        HIGHEST
     * @ignoreCancelled true
     */
    public function onMove(PlayerMoveEvent $ev): void {
        $p = $ev->getPlayer();
        //$p->sendPopup("YAW: " . ceil($p->getYaw()) . " DIRECTION: " . $p->getDirection());
        if(isset($this->bossbars[($k = $p->getName())])) {
            ($bb = $this->bossbars[$k])->setTitle($this->renderFor($p), false);
            $bb->updateFor($p);
        }
    }

    public function renderFor(Player $p): string {
        $yaw = (int)ceil(((int) ($p->getLocation()->getYaw() + 180)) % 360);
        if($yaw < 0) {
            $yaw += 360;
        }
        return $this->bars[$yaw];
    }

    public function onJoin(PlayerJoinEvent $ev): void {
        $p = $ev->getPlayer();
        $this->showBossbarTo($p);
    }

    public function showBossbarTo(Player $p): void {
        $bb = $this->bossbars[$p->getName()] = new BossBar($this->renderFor($p));
        $bb->showTo($p, false);
    }

    public function onLeave(PlayerQuitEvent $ev): void {
        $p = $ev->getPlayer();
        if(isset($this->bossbars[$p->getName()])) {
            $this->hideBossbarFrom($p);
        }
    }

    public function hideBossbarFrom(Player $p): void {
        $this->bossbars[($k = $p->getName())]->hideFrom($p);
        unset($this->bossbars[$k]);
    }
}