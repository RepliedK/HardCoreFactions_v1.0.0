<?php
/**
 * Created by PhpStorm.
 * User: CortexPE
 * Date: 2/9/2019
 * Time: 5:59 PM
 */

namespace hcf\utils\bossbar;


use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;

final class BossBarTracker implements Listener {
	/** @var BossBarTracker|null */
	private static $instance = null;
	/** @var BossBar[][] */
	private $bossBars = [];
	/** @var PluginBase */
	private $plugin;

	public static function init(Plugin $plugin): void {
		if(self::isInitialized()) return;
		Server::getInstance()->getPluginManager()->registerEvents((self::$instance = new BossBarTracker($plugin)), $plugin);
	}

	private function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
	}

	public static function isInitialized(): bool {
		return self::$instance !== null;
	}

	/**
	 * @return BossBarTracker|null
	 */
	public static function getInstance(): ?BossBarTracker {
		return self::$instance;
	}

	public function linkBossBar(Player $p, BossBar $bar): void {
		$this->bossBars[$p->getName()][$bar->getEntityId()] = $bar;
	}

	public function unlinkBossBar(Player $p, BossBar $bar): void {
		unset($this->bossBars[$p->getName()][$bar->getEntityId()]);
	}

	/**
	 * @param EntityTeleportEvent $ev
	 *
	 * @priority MONITOR
	 * @handleCancelled false
	 */
	public function onLevelChange(EntityTeleportEvent $ev): void {
		$p = $ev->getEntity();
		if($p instanceof Player && $ev->getFrom()->getWorld() !== $ev->getTo()->getWorld()) {
			foreach($this->bossBars[$p->getName()] ?? [] as $bar) {
				$bar->hideFrom($p);
				$this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($bar, $p): void {
					if(!$p->isOnline())return;
					$bar->showTo($p);
				}), 100);
			}
		}
	}

	/**
	 * @param PlayerQuitEvent $ev
	 *
	 * @priority MONITOR
	 */
	public function onLeave(PlayerQuitEvent $ev): void {
		unset($this->bossBars[$ev->getPlayer()->getName()]);
	}
}