<?php

declare(strict_types=1);

namespace hcf\handler\kit\command;

use hcf\handler\kit\command\subcommand\CreateSubCommand;
use hcf\handler\kit\command\subcommand\DeleteSubCommand;
use hcf\handler\kit\command\subcommand\EditSubCommand;
use hcf\utils\display\Inventories;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

/**
 * Class KitCommand
 * @package hcf\handler\kit\command
 */
class KitCommand extends Command
{
    
    /** @var KitSubCommand[] */
    private array $subCommands = [];
    
    /**
     * KitCommand construct.
     */
    public function __construct()
    {
        parent::__construct('kit', 'Kit commands');
        $this->setAliases(['gkit']);
        //$this->setPermission('kit.command');
        $this->setPermission("use.player.command");
        
        $this->subCommands['create'] = new CreateSubCommand;
        $this->subCommands['delete'] = new DeleteSubCommand;
        $this->subCommands['edit'] = new EditSubCommand;
    }
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!isset($args[0])) {
            Inventories::createKitOrganization($sender);
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
     * @param CommandSender $player
     * @param string $command
     * @return bool
     */
    private function checkPermissionByCommand(CommandSender $player, string $command): bool
    {
        return $player->hasPermission('kit.command.' . $command);
    }
}