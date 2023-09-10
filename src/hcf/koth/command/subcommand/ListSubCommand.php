<?php

declare(strict_types=1);

namespace hcf\koth\command\subcommand;

use hcf\koth\command\KothSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

/**
 * Class ListSubCommand
 * @package hcf\koth\command\subcommand
 */
class ListSubCommand implements KothSubCommand
{
    
    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function execute(CommandSender $sender, array $args): void
    {

        $kothManager = HCFLoader::getInstance()->getKothManager();
        $sender->sendMessage(TextFormat::colorize('&e× KOTH LIST ×'));
        
        foreach ($kothManager->getKoths() as $name => $koth) {
            $sender->sendMessage(TextFormat::colorize('&e' . $name . ' &f' . ($koth->getCoords() ?? 'None')));
        }
    }
}