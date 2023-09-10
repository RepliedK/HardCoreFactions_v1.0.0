<?php

declare(strict_types=1);

namespace hcf\command;

use hcf\player\Player;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\item\Armor;
use pocketmine\item\Tool;
use pocketmine\utils\TextFormat;

/**
 * Class RenameCommand
 * @package hcf\command
 */
class RenameCommand extends Command
{
	
    /**
     * RenameCommand construct.
     */
    public function __construct()
    {
        parent::__construct('rename', 'Command for rename');
        $this->setPermission('rename.command');
    }
	
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$sender instanceof Player)
            return;
            
        if (!$this->testPermission($sender))
            return;
        
        if (count($args) < 1) {
            $sender->sendMessage(TextFormat::colorize('&cUse /rename [name]'));
            return;
        }
        $item = clone $sender->getInventory()->getItemInHand();
        $name = implode(' ', $args);
        
        if (!$item instanceof Tool && !$item instanceof Armor) {
            $sender->sendMessage(TextFormat::colorize('You have no armor and no tools in your hand'));
            return;
        }
        $item->setCustomName(TextFormat::colorize($name));
        $sender->getInventory()->setItemInHand($item);
        $sender->sendMessage(TextFormat::colorize('&aYou have successfully renamed the item'));
    }
}