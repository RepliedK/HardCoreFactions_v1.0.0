<?php

declare(strict_types=1);

namespace hcf\handler\crate\command;

use hcf\handler\crate\command\subcommand\CreateSubCommand;
use hcf\handler\crate\command\subcommand\DeleteSubCommand;
use hcf\handler\crate\command\subcommand\EditSubCommand;
use hcf\handler\crate\command\subcommand\GiveSubCommand;
use hcf\handler\crate\command\subcommand\GiveKeySubCommand;
use hcf\handler\crate\command\subcommand\ItemConfigurationSubCommand;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

/**
 * Class CrateCommand
 * @package hcf\handler\crate\command
 */
class CrateCommand extends Command
{
    
    /** @var CrateSubCommand[] */
    private array $subCommands = [];
    
    /**
     * CrateCommand construct.
     */
    public function __construct()
    {
        parent::__construct('crate', 'Crate commands');
        $this->setPermission('crate.command');
        
        $this->subCommands['create'] = new CreateSubCommand;
        $this->subCommands['delete'] = new DeleteSubCommand;
        $this->subCommands['edit'] = new EditSubCommand;
        $this->subCommands['give'] = new GiveSubCommand;
        $this->subCommands['givekey'] = new GiveKeySubCommand;
        $this->subCommands['itemconfig'] = new ItemConfigurationSubCommand;
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
        return $player->hasPermission('crate.command.' . $command);
    }
}