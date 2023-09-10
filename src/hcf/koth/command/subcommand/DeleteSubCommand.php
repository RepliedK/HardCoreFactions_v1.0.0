<?php

declare(strict_types=1);

namespace hcf\koth\command\subcommand;

use hcf\koth\command\KothSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

/**
 * Class DeleteSubCommand
 * @package hcf\koth\command\subcommand
 */
class DeleteSubCommand implements KothSubCommand
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
            $sender->sendMessage(TextFormat::colorize('&c/koth delete [string: name]'));
            return;
        }
        $name = $args[0];
        
        if (HCFLoader::getInstance()->getKothManager()->getKoth($name) === null) {
            $sender->sendMessage(TextFormat::colorize('&cThe koth does not exist '));
            return;
        }
        HCFLoader::getInstance()->getKothManager()->removeKoth($name);
        $sender->sendMessage(TextFormat::colorize('&cYou have successfully removed the koth ' . $name));
    }
}