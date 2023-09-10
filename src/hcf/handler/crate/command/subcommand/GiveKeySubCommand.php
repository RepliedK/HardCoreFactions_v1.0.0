<?php

declare(strict_types=1);

namespace hcf\handler\crate\command\subcommand;

use hcf\handler\crate\command\CrateSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

/**
 * Class GiveKeySubCommand
 * @package hcf\handler\crate\command\subcommand
 */
class GiveKeySubCommand implements CrateSubCommand
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
            $sender->sendMessage(TextFormat::colorize('&cUse /crate giveKey [string: crateName] [string: playerName] [int: amount]'));
            return;
        }
        $crateName = $args[0];
        $playerName = $args[1];
        $amount = $args[2];
        
        $crate = HCFLoader::getInstance()->getHandlerManager()->getCrateManager()->getCrate($crateName);
        
        if ($crate === null) {
            $sender->sendMessage(TextFormat::colorize('&cThis crate does not exist'));
            return;
        }
        
        if ($playerName === 'all') {
            if (!is_numeric($amount)) {
                $sender->sendMessage(TextFormat::colorize('&cAmount invalid'));
                return;
            }
            
            foreach ($sender->getServer()->getOnlinePlayers() as $player) {
                $crate->giveKey($player, (int) $amount);
                $player->sendMessage(TextFormat::colorize('&aYou have received ' . $amount . 'x of ' . $crate->getKeyFormat()));
            }
        } else {
            $player = HCFLoader::getInstance()->getServer()->getPlayerExact($playerName);
        
            if ($player === null) {
                $sender->sendMessage(TextFormat::colorize('&cThis player does not exist. Please enter the full nickname'));
                return;
            }
        
            if (!is_numeric($amount)) {
                $sender->sendMessage(TextFormat::colorize('&cAmount invalid'));
                return;
            }
            $crate->giveKey($player, (int) $amount);
            $player->sendMessage(TextFormat::colorize('&aYou have received ' . $amount . 'x of ' . $crate->getKeyFormat()));
            $sender->sendMessage(TextFormat::colorize('&aYou have given ' . $player->getName() . ' ' . $amount . 'x amount of ' . $crate->getKeyFormat()));
        }
    }
}