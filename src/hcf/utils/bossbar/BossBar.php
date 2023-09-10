<?php

/***
 *
 *    _____             ____        ____
 *   / ___/__  ______  / __ \_   __/ __ \
 *   \__ \/ / / / __ \/ /_/ / | / / /_/ /
 *  ___/ / /_/ / / / / ____/| |/ / ____/
 * /____/\__, /_/ /_/_/     |___/_/
 *      /____/
 *
 * Copyright (C) LegacyPvP - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential


 */

declare(strict_types=1);

namespace hcf\utils\bossbar;


use InvalidArgumentException;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\FloatMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\LongMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\player\Player;

final class BossBar {
	/** @var int */
	protected $entityId;
	/** @var array */
	protected $metadata = [];
	/** @var Player[] */
	protected $viewers = [];
	/** @var string */
	protected $title;
	/** @var float */
	protected $value = 1;
	/** @var bool */
	protected $titleChanged = false;
	/** @var bool */
	protected $valueChanged = false;

	public function __construct(string $title = "BossBar") {
		if(!BossBarTracker::isInitialized()) {
			throw new \RuntimeException(BossBarTracker::class . " has not yet been initialized. Please call " . BossBarTracker::class . "::init() before using any boss bar.");
		}
		$this->title = $title;

		$this->metadata = [
			EntityMetadataProperties::FLAGS => new LongMetadataProperty((1 << EntityMetadataFlags::SILENT) | (1 << EntityMetadataFlags::INVISIBLE)),
			EntityMetadataProperties::BOUNDING_BOX_WIDTH => new FloatMetadataProperty(0),
			EntityMetadataProperties::BOUNDING_BOX_HEIGHT => new FloatMetadataProperty(0),
		];
		$this->regenerateEntityId();
	}

	public function regenerateEntityId(): void {
		$this->entityId = Entity::nextRuntimeId();
	}

	/**
	 * @return int
	 */
	public function getEntityId(): int {
		return $this->entityId;
	}

	/**
	 * @return float
	 */
	public function getValue(): float {
		return $this->value;
	}

	public function setValue(float $value, bool $update = true): void {
		if($value < 0 || $value > 1) {
			throw new InvalidArgumentException("Value must be between the range: (0 - 1)");
		}
		$this->value = $value;
		$this->valueChanged = true;
		if($update) {
			$this->updateForAll();
		}
	}

	public function updateForAll(): void {
		foreach($this->viewers as $player) {
			$this->updateFor($player, true);
		}
		$this->titleChanged = false;
		$this->valueChanged = false;
	}

	public function updateFor(Player $player, bool $batched = false) {
		if($this->titleChanged) {
			$player->getNetworkSession()->sendDataPacket($this->createBossEventPacket(BossEventPacket::TYPE_TITLE));
		}
		if($this->valueChanged) {
			$player->getNetworkSession()->sendDataPacket($this->createBossEventPacket(BossEventPacket::TYPE_HEALTH_PERCENT));
		}
		if(!$batched) {
			$this->titleChanged = false;
			$this->valueChanged = false;
		}
	}

	private function createBossEventPacket(int $eventType): BossEventPacket {
		$pk = new BossEventPacket(); // stuff isn't encoded in if it's not needed by the event type... tnx PM
		$pk->bossActorUniqueId = $this->entityId;
		$pk->eventType = $eventType;

		$pk->title = $this->getTitle();
		$pk->healthPercent = $this->value;

		$pk->color = $pk->overlay = 0;
		$pk->darkenScreen = false;

		return $pk;
	}

	public function getTitle(): string {
		return $this->title;
	}

	public function setTitle(string $title, bool $update = true) {
		$this->title = $title;
		$this->titleChanged = true;
		if($update) {
			$this->updateForAll();
		}
	}

	/**
	 * @param Player $p
	 *
	 * @return bool
	 */
	public function isViewer(Player $p): bool {
		return in_array($p, array_values($this->viewers), true);
	}

	public function showTo(Player $player, bool $isViewer = true) {
		$pk = AddActorPacket::create(
			$this->entityId,
			$this->entityId,
			EntityIds::SLIME,
			$player->getPosition()->subtract(0, 28, 0), // spawn under the player
			new Vector3(0, 0, 0),
			0.0,
			0.0,
			0.0,
			0.0,
			[],
			$this->metadata,
			new PropertySyncData([], []),
			[]
		);
		($ns = $player->getNetworkSession())->sendDataPacket($pk);
		$ns->sendDataPacket($this->createBossEventPacket(BossEventPacket::TYPE_SHOW));

		if($isViewer) {
			$this->viewers[$player->getName()] = $player;
		}
		BossBarTracker::getInstance()->linkBossBar($player, $this);
	}

	public function hideFrom(Player $player) {
		$pk = RemoveActorPacket::create($this->entityId);

		($ns = $player->getNetworkSession())->sendDataPacket($this->createBossEventPacket(BossEventPacket::TYPE_HIDE));
		$ns->sendDataPacket($pk);

		if(isset($this->viewers[$player->getName()])) {
			unset($this->viewers[$player->getName()]);
		}
		BossBarTracker::getInstance()->unlinkBossBar($player, $this);
	}
}