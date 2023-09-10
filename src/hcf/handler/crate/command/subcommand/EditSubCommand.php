<?php

declare(strict_types=1);

namespace hcf\handler\crate\command\subcommand;

use hcf\handler\crate\command\CrateSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;
use hcf\utils\display\Inventories;

use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;

/**
 * Class EditSubCommand
 * @package hcf\handler\crate\command\subcommand
 */
class EditSubCommand implements CrateSubCommand
{
    
    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player)
            return;
            
        if (count($args) < 2) {
            $sender->sendMessage(TextFormat::colorize('&cUse /crate edit [string: crateName] [string: keyId:keyFormat:nameFormat:items]'));
            return;
        }
        $crateName = $args[0];
        $type = $args[1];
        
        $crate = HCFLoader::getInstance()->getHandlerManager()->getCrateManager()->getCrate($crateName);
        
        if ($crate === null) {
            $sender->sendMessage(TextFormat::colorize('&cThis crate does not exist'));
            return;
        }
        
        switch($type) {
            case 'key':
                $item = $sender->getInventory()->getItemInHand();
        
                if (!$item instanceof Item) {
                    $sender->sendMessage(TextFormat::colorize('&cInvalid keyId data'));
                    return;
                }
                $crate->setKeyId($item);
                $sender->sendMessage(TextFormat::colorize('&akey of the crate ' . $crate->getName() . ' has been modified successfully'));
                break;
                
            case 'keyFormat':
                if (count($args) < 3) {
                    $sender->sendMessage(TextFormat::colorize('&cUse /crate edit ' . $crateName . ' keyFormat [string: format]'));
                    return;
                }
                $keyFormat = $args[2];
                
                $crate->setKeyFormat($keyFormat);
                $sender->sendMessage(TextFormat::colorize('&akeyFormat of the crate ' . $crate->getName() . ' has been modified successfully'));
                break;
                
            case 'nameFormat':
                if (count($args) < 3) {
                    $sender->sendMessage(TextFormat::colorize('&cUse /crate edit ' . $crateName . ' nameFormat [string: format]'));
                    return;
                }
                $nameFormat = $args[2];
                
                $crate->setNameFormat($nameFormat);
                $sender->sendMessage(TextFormat::colorize('&anameFormat of the crate ' . $crate->getName() . ' has been modified successfully'));
                break;
                
            case 'items':
                Inventories::editCrateContent($sender, $crateName);
                break;
                
            default:
                $sender->sendMessage(TextFormat::colorize('&cUse /crate edit ' . $crateName . ' [string: keyId:keyFormat:nameFormat:items]'));
                break;
        }
    }
}