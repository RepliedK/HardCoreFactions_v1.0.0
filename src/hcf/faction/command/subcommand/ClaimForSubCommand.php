<?php

declare(strict_types=1);

namespace hcf\faction\command\subcommand;

use hcf\faction\command\FactionSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\command\CommandSender;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;

class ClaimForSubCommand implements FactionSubCommand
{
    
    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player)
            return;
            
        if (!$sender->hasPermission('faction.command.claimfor')) {
            $sender->sendMessage(TextFormat::colorize('&cThis sub command does not exist'));
            return;
        }
        
        if (count($args) < 2) {
            if (($creator = HCFLoader::getInstance()->getClaimManager()->getCreator($sender->getName())) !== null) {
                if (!$creator->isValid()) {
                    $sender->sendMessage(TextFormat::colorize('&cYou have not selected the claim'));
                    return;
                }
                $creator->deleteCorners($sender);
                HCFLoader::getInstance()->getClaimManager()->createClaim($creator->getName(), $creator->getType(), $creator->getMinX(), $creator->getMaxX(), $creator->getMinZ(), $creator->getMaxZ(), $creator->getWorld());
                $sender->sendMessage(TextFormat::colorize('&aYou have made the claim of the opclaim ' . $creator->getName()));
                HCFLoader::getInstance()->getClaimManager()->removeCreator($sender->getName());
            
                foreach ($sender->getInventory()->getContents() as $slot => $i) {
                    if ($i->getNamedTag()->getTag('claim_type')) {
                        $sender->getInventory()->clear($slot);
                        break;
                    }
                }
                return;
            }
            $sender->sendMessage(TextFormat::colorize("&aClaim Suggest Types: spawn, road and custom"));
            $sender->sendMessage(TextFormat::colorize('&cUse /faction claimfor [string: name] [string: type]'));
            return;
        }
        $claimName = $args[0];
        $claimType = $args[1];
        
        if ($claimName === 'cancel') {
            if (($creator = HCFLoader::getInstance()->getClaimManager()->getCreator($sender->getName())) !== null) {
                $creator->deleteCorners($sender);
                HCFLoader::getInstance()->getClaimManager()->removeCreator($sender->getName());
                $sender->sendMessage(TextFormat::colorize('&cYou have canceled the claim'));
            } else
                $sender->sendMessage(TextFormat::colorize('&cYou are not in claim mode yet'));
            return;
        }
        
        if (HCFLoader::getInstance()->getClaimManager()->getCreator($sender->getName()) !== null) {
            $sender->sendMessage(TextFormat::colorize('&cYou are already creating a claim'));
            return;
        }
        
        if (HCFLoader::getInstance()->getFactionManager()->getFaction($claimName) === null)
            HCFLoader::getInstance()->getFactionManager()->createFaction($claimName, [
                'roles' => [],
                'dtr' => 1.01,
                'balance' => 0,
                'points' => 0,
                'kothCaptures' => 0,
                'timeRegeneration' => null,
                'home' => null,
                'claim' => null
            ]);
        $item = VanillaItems::GOLDEN_HOE()->setCustomName(TextFormat::colorize('&eClaim selector'));
        $item->setNamedTag($item->getNamedTag()->setString('claim_type', $claimType));
        
        if (!$sender->getInventory()->canAddItem($item)) {
            $sender->sendMessage(TextFormat::colorize('&cYou cannot add the item to make the claim to your inventory'));
            return;
        }
        $sender->getInventory()->addItem($item);
        HCFLoader::getInstance()->getClaimManager()->createCreator($sender->getName(), $claimName, $claimType);
        $sender->sendMessage(TextFormat::colorize('&aNow you can claim the area'));
    }
}
