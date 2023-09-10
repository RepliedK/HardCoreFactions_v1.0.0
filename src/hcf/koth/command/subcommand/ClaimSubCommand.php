<?php

declare(strict_types=1);

namespace hcf\koth\command\subcommand;

use hcf\koth\command\KothSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;

use pocketmine\command\CommandSender;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;

/**
 * Class ClaimSubCommand
 * @package hcf\koth\command\subcommand
 */
class ClaimSubCommand implements KothSubCommand
{
    
    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player)
            return;
        
        if (count($args) < 1) {
            if (($creator = HCFLoader::getInstance()->getClaimManager()->getCreator($sender->getName())) !== null && $creator->getType() === 'koth') {
                if (!$creator->isValid()) {
                    $sender->sendMessage(TextFormat::colorize('&cYou have not selected the claim'));
                    return;
                }
                $creator->deleteCorners($sender);
                HCFLoader::getInstance()->getClaimManager()->createClaim($creator->getName(), $creator->getType(), $creator->getMinX(), $creator->getMaxX(), $creator->getMinZ(), $creator->getMaxZ(), $creator->getWorld());
                $sender->sendMessage(TextFormat::colorize('&aYou have made the claim of the koth ' . $creator->getName()));
                HCFLoader::getInstance()->getClaimManager()->removeCreator($sender->getName());
            
                foreach ($sender->getInventory()->getContents() as $slot => $i) {
                    if ($i->getNamedTag()->getTag('claim_type')) {
                        $sender->getInventory()->clear($slot);
                        break;
                    }
                }
                return;
            }
            $sender->sendMessage(TextFormat::colorize('&c/koth claim [string: name]'));
            return;
        }
        
        if ($args[0] === 'cancel') {
            if (($creator = HCFLoader::getInstance()->getClaimManager()->getCreator($sender->getName())) !== null && $creator->getType() === 'koth') {
                HCFLoader::getInstance()->getClaimManager()->removeCreator($sender->getName());
                $sender->sendMessage(TextFormat::colorize('&cYou have canceled the claim'));
            } else
                $sender->sendMessage(TextFormat::colorize('&cYou are not in claim mode yet'));
            return;
        }
        $name = $args[0];
        
        if (HCFLoader::getInstance()->getClaimManager()->getCreator($sender->getName()) !== null) {
            $sender->sendMessage(TextFormat::colorize('&cYou are already creating a capzone'));
            return;
        }
        
        if (HCFLoader::getInstance()->getKothManager()->getKoth($name) === null) {
            $sender->sendMessage(TextFormat::colorize('&cThe koth does not exist'));
            return;
        }
        $item = VanillaItems::GOLDEN_HOE()->setCustomName(TextFormat::colorize('&eClaim selector'));
        $item->setNamedTag($item->getNamedTag()->setString('claim_type', 'koth'));
        
        if (!$sender->getInventory()->canAddItem($item)) {
            $sender->sendMessage(TextFormat::colorize('&cYou cannot add the item to make the claim to your inventory'));
            return;
        }
        $sender->getInventory()->addItem($item);
        HCFLoader::getInstance()->getClaimManager()->createCreator($sender->getName(), $name, 'koth');
        $sender->sendMessage(TextFormat::colorize('&aNow you can claim the area'));
    }
}