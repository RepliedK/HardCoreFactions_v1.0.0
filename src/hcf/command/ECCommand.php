<?php

declare(strict_types=1);

namespace hcf\command;

use hcf\player\Player as HCFPlayer;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\inventory\Inventory;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class ECCommand extends Command
{
    
    /**
     * ECCommand construct.
     */
    public function __construct()
    {
        parent::__construct('ec', 'Command for Ender Chest');
        $this->setPermission("use.player.command");
    }
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$sender instanceof HCFPlayer)
            return;
        
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST);
        $menu->getInventory()->setContents($sender->getEnderInventory()->getContents());
        $menu->setInventoryCloseListener(function (Player $player, Inventory $inventory): void {
            $player->getEnderInventory()->setContents($inventory->getContents());
        });
        
        $menu->send($sender, TextFormat::colorize('&4Ender chest'));
    }
}