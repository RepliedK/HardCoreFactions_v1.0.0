<?php

declare(strict_types=1);

namespace hcf\handler\crate\command\subcommand;

use hcf\handler\crate\command\CrateSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;
use hcf\utils\display\Inventories;
use pocketmine\block\VanillaBlocks;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;

/**
 * Class CreateSubCommand
 * @package hcf\handler\crate\command\subcommand
 */
class CreateSubCommand implements CrateSubCommand
{
    
    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player)
            return;
        
        if (count($args) < 3) {
            $sender->sendMessage(TextFormat::colorize('&c/crate create [string: crateName] [string: keyFormat] [string: nameFormat]'));
            return;
        }
        $crateName = $args[0];
        $keyFormat = $args[1];
        $nameFormat = $args[2];
        
        $item = $sender->getInventory()->getItemInHand();
        
        if (!$item instanceof Item) {
            $sender->sendMessage(TextFormat::colorize('&cInvalid keyId data'));
            return;
        }
        
        if (HCFLoader::getInstance()->getHandlerManager()->getCrateManager()->getCrate($crateName) !== null) {
            $sender->sendMessage(TextFormat::colorize('&cThis crate already exists'));
            return;
        }
        $data = [
            'crateName' => $crateName,
            'key' => $item,
            'keyFormat' => $keyFormat,
            'nameFormat' => $nameFormat
        ];
        Inventories::createCrateContent($sender, $data);
    }
}