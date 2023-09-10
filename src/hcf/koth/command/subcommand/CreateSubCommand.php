<?php

declare(strict_types=1);

namespace hcf\koth\command\subcommand;

use hcf\koth\command\KothSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;
use hcf\utils\logic\time\Timer;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

/**
 * Class CreateSubCommand
 * @package hcf\koth\command\subcommand
 */
class CreateSubCommand implements KothSubCommand
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
            $sender->sendMessage(TextFormat::colorize('&c/koth create [string: name] [string: time]'));
            return;
        }
        $name = $args[0];
        $time = $args[1];
        
        if (HCFLoader::getInstance()->getKothManager()->getKoth($name) !== null) {
            $sender->sendMessage(TextFormat::colorize('&cThe koth already exists'));
            return;
        }
        
        $time = Timer::time($time);
        $sender->sendMessage(TextFormat::colorize('&aYou have successfully created the koth ' . $name));
        HCFLoader::getInstance()->getKothManager()->createKoth($name, (int) $time);
    }
}