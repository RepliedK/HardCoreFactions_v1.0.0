<?php

declare(strict_types=1);

namespace hcf\timer\command;

use hcf\HCFLoader;
use hcf\utils\logic\time\Timer;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

/**
 * Class EotwCommand
 * @package hcf\timer\command
 */
class EotwCommand extends Command
{
    
    /**
     * EotwCommand construct.
     */
    public function __construct()
    {
        parent::__construct('eotw', 'Command for eotw');
        $this->setPermission('eotw.command');
    }
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$this->testPermission($sender))
            return;
            
        if (count($args) < 1) {
            $sender->sendMessage(TextFormat::colorize('&cUse /eotw help'));
            return;
        }
        
        switch (strtolower($args[0])) {
            case 'help':
                $sender->sendMessage(
                    TextFormat::colorize('&eEotw Commands') . "\n" .
                    TextFormat::colorize('&7/eotw start [time] - &eUse this command to start the eotw') . "\n" .
                    TextFormat::colorize('&7/eotw stop - &eUse this command to stop eotw')
                );
                break;
            
            case 'start':
                if (HCFLoader::getInstance()->getTimerManager()->getEotw()->isActive()) {
                    $sender->sendMessage(TextFormat::colorize('&cThe eotw is already started'));
                    return;
                }
                
                if (count($args) < 2) {
                    $sender->sendMessage(TextFormat::colorize('&cUse /eotw start [time]'));
                    return;
                }
                $time = $args[1];
                
                $time = Timer::time($time);
                HCFLoader::getInstance()->getTimerManager()->getEotw()->setActive(true);
                HCFLoader::getInstance()->getTimerManager()->getEotw()->setTime((int) $time);
                $sender->sendMessage(TextFormat::colorize('&aThe eotw has started!'));
                break;
            
            case 'stop':
                if (!HCFLoader::getInstance()->getTimerManager()->getEotw()->isActive()) {
                    $sender->sendMessage(TextFormat::colorize('&cThe eotw has not started'));
                    return;
                }
                HCFLoader::getInstance()->getTimerManager()->getEotw()->setActive(false);
                $sender->sendMessage(TextFormat::colorize('&cYou have turned off the eotw'));
                break;
            
            default:
                $sender->sendMessage(TextFormat::colorize('&cUse /eotw help'));
                break;
        }
    }
}