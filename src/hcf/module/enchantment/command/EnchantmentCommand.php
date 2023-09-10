<?php

declare(strict_types=1);

namespace hcf\module\enchantment\command;

use hcf\module\enchantment\command\subcommand\AddSubCommand;
use hcf\module\enchantment\command\subcommand\RemoveSubCommand;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

/**
 * Class EnchantmentCommand
 * @package hcf\module\enchantment\command
 */
class EnchantmentCommand extends Command
{
    
    /** @var EnchantmentSubCommand[] */
    private array $subCommands = [];
    
    /**
     * EnchantmentCommand construct.
     */
    public function __construct()
    {
        parent::__construct('customenchant', 'Custom enchant commands', null, ['ce']);
        $this->setPermission('custom.enchant.command');
        
        $this->subCommands['add'] = new AddSubCommand;
        $this->subCommands['remove'] = new RemoveSubCommand;
    }
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!isset($args[0])) {
            return;
        }
        $subCommand = $this->subCommands[$args[0]] ?? null;
        
        if ($subCommand === null) {
            $sender->sendMessage(TextFormat::colorize('&cThis sub command does not exist'));
            return;
        }
        
        if (!$this->checkPermissionByCommand($sender, $args[0])) {
            $sender->sendMessage(TextFormat::colorize('&cYou do not have permission to use this command'));
            return;
        }
        array_shift($args);
        $subCommand->execute($sender, $args);
    }
    
    /**
     * @param string $command
     * @return bool
     */
    private function checkPermissionByCommand(CommandSender $player, string $command): bool
    {
        return $player->hasPermission('custom.enchant.command.' . $command);
    }
}