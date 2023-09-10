<?php

declare(strict_types=1);

namespace hcf\timer\command;

use hcf\HCFLoader;
use hcf\utils\logic\time\Timer;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

/**
 * Class SotwCommand
 * @package hcf\timer\command
 */
class SotwCommand extends Command
{
    
    /**
     * SotwCommand construct.
     */
    public function __construct()
    {
        parent::__construct('sotw', 'Command for sotw');
        $this->setPermission('sotw.command');
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
            $sender->sendMessage(TextFormat::colorize('&cUse /sotw help'));
            return;
        }
        
        switch (strtolower($args[0])) {
            case 'help':
                $sender->sendMessage(
                    TextFormat::colorize('&eSotw Commands') . "\n" .
                    TextFormat::colorize('&7/sotw start [time] - &eUse this command to start the sotw') . "\n" .
                    TextFormat::colorize('&7/sotw stop - &eUse this command to stop sotw')
                );
                break;
            
            case 'start':
                if (HCFLoader::getInstance()->getTimerManager()->getSotw()->isActive()) {
                    $sender->sendMessage(TextFormat::colorize('&cThe sotw is already started'));
                    return;
                }
                
                if (count($args) < 2) {
                    $sender->sendMessage(TextFormat::colorize('&cUse /sotw start [time]'));
                    return;
                }
                $time = $args[1];

                $time = Timer::time($time);
                HCFLoader::getInstance()->getTimerManager()->getSotw()->setActive(true);
                HCFLoader::getInstance()->getTimerManager()->getSotw()->setTime((int) $time);
                $sender->sendMessage(TextFormat::colorize('&aThe sotw has started!'));
                break;
            
            case 'stop':
                if (!HCFLoader::getInstance()->getTimerManager()->getSotw()->isActive()) {
                    $sender->sendMessage(TextFormat::colorize('&cThe sotw has not started'));
                    return;
                }
                HCFLoader::getInstance()->getTimerManager()->getSotw()->setActive(false);
                $sender->sendMessage(TextFormat::colorize('&cYou have turned off the sotw'));
                break;
            
            default:
                $sender->sendMessage(TextFormat::colorize('&cUse /sotw help'));
                break;
        }
    }
}