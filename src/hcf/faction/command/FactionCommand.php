<?php

declare(strict_types=1);

namespace hcf\faction\command;

use hcf\entity\custom\CustomItemEntity;
use hcf\entity\custom\TextEntity;

use hcf\faction\command\subcommand\AcceptInviteSubCommand;
use hcf\faction\command\subcommand\admin\ForceDisbandSubCommand;
use hcf\faction\command\subcommand\admin\SetDtrSubCommand;
use hcf\faction\command\subcommand\admin\SetPointsSubCommand;
use hcf\faction\command\subcommand\admin\SetRegenTimeSubCommand;
use hcf\faction\command\subcommand\admin\SetStrikesSubCommand;
use hcf\faction\command\subcommand\ChatSubCommand;
use hcf\faction\command\subcommand\CreateSubCommand;
use hcf\faction\command\subcommand\ClaimForSubCommand;
use hcf\faction\command\subcommand\ClaimSubCommand;
use hcf\faction\command\subcommand\DemoteSubCommand;
use hcf\faction\command\subcommand\DepositSubCommand;
use hcf\faction\command\subcommand\DisbandSubCommand;
use hcf\faction\command\subcommand\FocusSubCommand;
use hcf\faction\command\subcommand\HelpSubCommand;
use hcf\faction\command\subcommand\HomeSubCommand;
use hcf\faction\command\subcommand\InviteSubCommand;
use hcf\faction\command\subcommand\KickSubCommand;
use hcf\faction\command\subcommand\LeaveSubCommand;
use hcf\faction\command\subcommand\PromoteSubCommand;
use hcf\faction\command\subcommand\RallySubCommand;
use hcf\faction\command\subcommand\SetHomeSubCommand;
use hcf\faction\command\subcommand\StuckSubCommand;
use hcf\faction\command\subcommand\TopSubCommand;
use hcf\faction\command\subcommand\UnclaimSubCommand;
use hcf\faction\command\subcommand\UnfocusSubCommand;
use hcf\faction\command\subcommand\UnrallySubCommand;
use hcf\faction\command\subcommand\WhoSubCommand;
use hcf\faction\command\subcommand\WithdrawSubCommand;
use hcf\faction\command\subcommand\MapSubCommand;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class FactionCommand extends Command
{
    
    /** @var FactionSubCommand[] */
    private array $subCommands = [];
    
    /**
     * FactionCommand construct.
     */
    public function __construct()
    {
        parent::__construct('faction', 'Faction commands');
        $this->setAliases(['f']);
        $this->setPermission("use.player.command");
        
        $this->subCommands['accept'] = new AcceptInviteSubCommand;
        $this->subCommands['join'] = new AcceptInviteSubCommand;
        $this->subCommands['deposit'] = new DepositSubCommand;
        $this->subCommands['d'] = new DepositSubCommand;
        $this->subCommands['withdraw'] = new WithdrawSubCommand;
        $this->subCommands['w'] = new WithdrawSubCommand;
        $this->subCommands['create'] = new CreateSubCommand;
        $this->subCommands['claimfor'] = new ClaimForSubCommand;
        $this->subCommands['claim'] = new ClaimSubCommand;
        $this->subCommands['focus'] = new FocusSubCommand;
        $this->subCommands['help'] = new HelpSubCommand;
        $this->subCommands['home'] = new HomeSubCommand;
        $this->subCommands['hq'] = new HomeSubCommand;
        $this->subCommands['rally'] = new RallySubCommand;
        $this->subCommands['sethome'] = new SetHomeSubCommand;
        $this->subCommands['sethq'] = new SetHomeSubCommand;
        $this->subCommands['stuck'] = new StuckSubCommand;
        $this->subCommands['top'] = new TopSubCommand;
        $this->subCommands['unfocus'] = new UnfocusSubCommand;
        $this->subCommands['unrally'] = new UnrallySubCommand;
        $this->subCommands['who'] = new WhoSubCommand;
        $this->subCommands['invite'] = new InviteSubCommand;
        $this->subCommands['disband'] = new DisbandSubCommand;
        $this->subCommands['leave'] = new LeaveSubCommand;
        $this->subCommands['kick'] = new KickSubCommand;
        $this->subCommands['chat'] = new ChatSubCommand;
        $this->subCommands['c'] = new ChatSubCommand;
        $this->subCommands['unclaim'] = new UnclaimSubCommand;
        $this->subCommands['info'] = new WhoSubCommand;
        $this->subCommands['setdtr'] = new SetDtrSubCommand;
        $this->subCommands['setpoints'] = new SetPointsSubCommand;
        $this->subCommands['setregentime'] = new SetRegenTimeSubCommand;
        $this->subCommands['forcedisband'] = new ForceDisbandSubCommand;
        $this->subCommands['setstrikes'] = new SetStrikesSubCommand;
        $this->subCommands['promote'] = new PromoteSubCommand;
        $this->subCommands['demote'] = new DemoteSubCommand;
        $this->subCommands['map'] = new MapSubCommand;
    }
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!isset($args[0])) {
            /*foreach ($sender->getWorld()->getEntities() as $entity) {
                if ($entity instanceof CustomItemEntity || $entity instanceof TextEntity)
                    $entity->kill();
            }*/
            $sender->sendMessage(TextFormat::colorize('&cUse /f help'));
            return;
        }
        $subCommand = $this->subCommands[$args[0]] ?? null;
        
        if ($subCommand === null) {
            $sender->sendMessage(TextFormat::colorize('&cThis sub command does not exist'));
            return;
        }
        array_shift($args);
        $subCommand->execute($sender, $args);
    }
}