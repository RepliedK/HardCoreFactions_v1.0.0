<?php

namespace hcf\module\package\commands;

use hcf\HCFLoader;
use hcf\item\default\PartnerPackage;
use hcf\module\package\PackageManager;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat as TE;
use pocketmine\player\Player;

class PartnerPackagesCommand extends Command
{

    /**
     * ParterPackagesCommand constructor.
     */
    public function __construct()
    {
        parent::__construct('pkg', 'packges commands');
        $this->setPermission('pkg.command');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (count($args) === 0) {
            $sender->sendMessage(
                TE::GRAY . ("----------------------------------------------------------\n") .
                TE::colorize("\n") .
                TE::BLUE . "/pkg - " . TE::WHITE . ("use this command to get plugin information\n") .
                TE::BLUE . "/pkg give [all/player] [amount] - " . TE::WHITE . ("uste this command to give pkg\n") .
                TE::BLUE . "/pkg editcontent-" . TE::WHITE . ("use this command to edit the pkg content\n") .
                TE::colorize("\n") .
                TE::GRAY . ("----------------------------------------------------------\n")
            );
            return;
        }
        
        switch ($args[0]) {
            case "editcontent":
                if (!$sender->hasPermission("pkg.command")) {
                    $sender->sendMessage(TE::RED . "You don't have permissions");
                    return;
                }
                
                if (!$sender instanceof Player) {
                    $sender->sendMessage(TE::RED . "This message can only be executed in game!");
                    return;
                }
                $player = HCFLoader::getInstance()->getServer()->getPlayerExact($sender->getName());
                PackageManager::getPartnerPackage()->setItems($player->getInventory()->getContents());
                $sender->sendMessage(TE::GREEN . "The content has been edited correctly");
                break;
                
            case "give":
                if (!$sender->hasPermission("pkg.command")) {
                    $sender->sendMessage(TE::RED . "You don't have permissions");
                    return;
                }
                if (empty($args[1])) {
                    $sender->sendMessage(TE::RED . "/pkg give [all/player] [amount]");
                    return;
                }
                if (empty($args[2])) {
                    $sender->sendMessage(TE::RED . "/pkg give [all/player] [amount]");
                    return;
                }
                $player = HCFLoader::getInstance()->getServer()->getPlayerExact($args[1]);
                if ($player !== null) {
                    $player->sendMessage(TE::colorize(HCFLoader::getInstance()->getConfig()->get('prefix'))."§7You have received §d§l" . $args[2] . " §r§7PartnerPackages for §d§l" . $sender->getName());
                    PartnerPackage::addPartner($player, $args[2]);
                    return;
                }
                foreach (HCFLoader::getInstance()->getServer()->getOnlinePlayers() as $player) {
                    PartnerPackage::addPartner($player, $args[2]);
                }
                HCFLoader::getInstance()->getServer()->broadcastMessage(TE::colorize(HCFLoader::getInstance()->getConfig()->get('prefix'))."§7All online players have received §d§l" . $args[2] . " §r§7PartnerPackages for §d§l" . $sender->getName());
                break;
        }
    }
}