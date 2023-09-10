<?php

declare(strict_types=1);

namespace hcf\handler\crate\command\subcommand;

use hcf\handler\crate\command\CrateSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\block\VanillaBlocks;
use pocketmine\command\CommandSender;
use pocketmine\item\ItemFactory;
use pocketmine\utils\TextFormat;

class GiveSubCommand implements CrateSubCommand
{
    
    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player)
            return;
            
        if (count($args) === 0) {
            $sender->sendMessage(TextFormat::colorize('&cUse /crate give [string: crateName]'));
            return;
        }
        $crateName = $args[0];
        $crate = HCFLoader::getInstance()->getHandlerManager()->getCrateManager()->getCrate($crateName);
        
        if ($crate === null) {
            $sender->sendMessage(TextFormat::colorize('&cThis crate does not exist'));
            return;
        }
        $chest = VanillaBlocks::CHEST()->asItem();
        $chest->setCustomName(TextFormat::colorize('Crate ' . $crateName));
        
        $namedtag = $chest->getNamedTag();
        $namedtag->setString('crate_place', $crateName);
        $chest->setNamedTag($namedtag);
            
        $sender->sendMessage(TextFormat::colorize('&aCrate ' . $crateName . ' given'));
        $sender->getInventory()->addItem($chest);
    }
}