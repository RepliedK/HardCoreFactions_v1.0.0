<?php

declare(strict_types=1);

namespace hcf\handler\crate;

use hcf\utils\logic\serialize\Serialize;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class Crate
{
    
    /** @var string */
    private string $name;

    private Item $keyId;
    /** @var string */
	private string $keyFormat;
	/** @var string */
	private string $nameFormat;
	/** @var Item[] */
    private array $items;
    
    /** @var array */
    public array $floatingTexts = [];
    
    /**
     * Crate construct.
     * @param string $name
     * @param string $keyId
     * @param string $keyFormat
     * @param string $nameFormat
     * @param Item[] $items
     */
    public function __construct(string $name, Item $keyId, string $keyFormat, string $nameFormat, array $items)
    {
        $this->name = $name;
        $this->keyId = $keyId;
        $this->keyFormat = $keyFormat;
        $this->nameFormat = $nameFormat;
        $this->items = $items;
    }
    
    /**
	 * @return string
	 */
    public function getName(): string
    {
        return $this->name;
    }
    
	public function getKeyId(): Item
	{
		return $this->keyId;
	}
    
    /**
	 * @return string
	 */
	public function getKeyFormat(): string
	{
		return $this->keyFormat;
	}
	
	/**
	 * @return string
	 */
	public function getNameFormat(): string
	{
		return $this->nameFormat;
	}
	
	/**
	 * @return Item[]
	 */
	public function getItems(): array
    {
        return $this->items;
    }
    
    /**
	 * @param string $keyId
	 */
	public function setKeyId(Item $keyId): void
	{
		$this->keyId = $keyId;
	}
	
	/**
	 * @param string $keyFormat
	 */
	public function setKeyFormat(string $keyFormat): void
	{
		$this->keyFormat = $keyFormat;
	}
	
	/**
	 * @param string $nameFormat
	 */
	public function setNameFormat(string $nameFormat): void
	{
		$this->nameFormat = $nameFormat;
	}
	
	/**
	 * @param Item[] $items
	 */
	public function setItems(array $items): void
    {
        $this->items = $items;
    }
    
    /**
     * @param Player $player
     * @param int $count
     * @return bool
     */
    public function giveKey(Player $player, int $count = 1): bool
    {
        $item = $this->getKeyId();
        $item->setCustomName(TextFormat::colorize($this->getKeyFormat()));
        $item->setCount($count);
        $item->setLore([
            TextFormat::GRAY . 'You can redeem this key at crate',
			TextFormat::GRAY . 'in the spawn area.',
			'',
			TextFormat::GRAY . TextFormat::ITALIC . 'Left click to view crate rewards.',
			TextFormat::GRAY . TextFormat::ITALIC . 'Right click to open the crate.',
        ]);
        $item->setNamedTag($item->getNamedTag()->setString('crate_name', $this->getName()));
        
        if (!$player->getInventory()->canAddItem($item))
            return false;
        $player->getInventory()->addItem($item);
        return true;
    }
    
    /**
     * @param Player $player
     * @return bool
     */
    public function giveReward(Player $player): bool
    {
        $items = $this->getItems();
        $randomItem = $items[array_rand($items)];
        
        if (!$player->getInventory()->canAddItem($randomItem))
            return false;
        $itemInHand = $player->getInventory()->getItemInHand();
        
        if($itemInHand->getCount() > 1){
            $itemInHand->setCount($itemInHand->getCount() - 1);
        }else{
            $itemInHand = VanillaItems::AIR();
        }
        $player->getInventory()->setItemInHand($itemInHand);
        $player->getInventory()->addItem($randomItem);
        return true;
    }
    
    /**
     * @return array
     */
    public function getData(): array
    {
        $data = [
            'key' => Serialize::serialize($this->getKeyId()),
            'keyFormat' => $this->getKeyFormat(),
            'nameFormat' => $this->getNameFormat(),
            'items' => []
        ];
        
        foreach ($this->getItems() as $slot => $item) {
            $data['items'][$slot] = Serialize::serialize($item);
        }
        return $data;
    }
}