<?php

declare(strict_types=1);

namespace hcf\timer\command;

use hcf\HCFLoader;
use hcf\utils\logic\time\Timer;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

/**
 * Class CustomTimerCommand
 * @package hcf\timer\command
 */
class CustomTimerCommand extends Command
{
    
    /**
     * CustomTimerCommand construct.
     */
    public function __construct()
    {
        parent::__construct('customtimer', 'Command for CustomsTimer');
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
            $sender->sendMessage(TextFormat::colorize('&cUse /customtimer help'));
            return;
        }
        
        switch (strtolower($args[0])) {
            case 'help':
                $sender->sendMessage(
                    TextFormat::colorize('&eCustomTimer Commands') . "\n" .
                    TextFormat::colorize('&7/customtimer start [name] [time] [format] - &eUse this command to start the timer') . "\n" .
                    TextFormat::colorize('&7/customtimer stop - &eUse this command to stop timer')
                );
                break;
            
            case 'start':
                
                if (count($args) < 4) {
                    $sender->sendMessage(TextFormat::colorize('&cUse /customtimer start [name] [time] [format]'));
                    return;
                }

                if(HCFLoader::getInstance()->getTimerManager()->hasCustomTimer($args[1])){
                    if (HCFLoader::getInstance()->getTimerManager()->getCustomTimerByName($args[1])->isActive()) {
                        $sender->sendMessage(TextFormat::colorize('&cThe timer is already started'));
                        return;
                    }
                    return;
                }

                $time = $args[2];
                
                $time = Timer::time($time);
                HCFLoader::getInstance()->getTimerManager()->addCustomTimer($args[1]);
                HCFLoader::getInstance()->getTimerManager()->getCustomTimerByName($args[1])->setActive(true);
                HCFLoader::getInstance()->getTimerManager()->getCustomTimerByName($args[1])->setTime((int) $time);
                HCFLoader::getInstance()->getTimerManager()->getCustomTimerByName($args[1])->setFormat($args[3]);
                $sender->sendMessage(TextFormat::colorize('&aThe timer has started!'));
                break;
            
            case 'stop':
                if (count($args) < 2) {
                    $sender->sendMessage(TextFormat::colorize('&cUse /customtimer stop [name]'));
                    return;
                }
                if(!HCFLoader::getInstance()->getTimerManager()->hasCustomTimer($args[1])){
                    $sender->sendMessage(TextFormat::colorize('&cThe timer has not exists'));
                    return;
                }
                HCFLoader::getInstance()->getTimerManager()->getCustomTimerByName($args[1])->setActive(false);
                $sender->sendMessage(TextFormat::colorize('&cYou have turned off the timer'));
                HCFLoader::getInstance()->getTimerManager()->removeCustomTimer($args[1]);
                break;
            
            default:
                $sender->sendMessage(TextFormat::colorize('&cUse /customtimer help'));
                break;
        }
    }
}