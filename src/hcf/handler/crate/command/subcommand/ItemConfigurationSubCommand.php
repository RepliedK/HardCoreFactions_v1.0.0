<?php

declare(strict_types=1);

namespace hcf\handler\crate\command\subcommand;

use hcf\handler\crate\command\CrateSubCommand;
use hcf\player\Player;

use pocketmine\command\CommandSender;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;

/**
 * Class ItemConfigurationSubCommand
 * @package hcf\handler\crate\command\subcommand
 */
class ItemConfigurationSubCommand implements CrateSubCommand
{
    
    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player)
            return;
        $item = VanillaItems::GOLDEN_AXE();
        $item->setCustomName(TextFormat::colorize('&4Crate Configuration'));
        $item->setNamedTag($item->getNamedTag()->setString('crate_configuration', 'true'));
        
        $sender->getInventory()->addItem($item);
    }
}