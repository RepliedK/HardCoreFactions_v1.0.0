<?php

declare(strict_types=1);

namespace hcf\command;

use hcf\HCFLoader;

/**
 * Class CommandManager
 * @package hcf\command
 */
class CommandManager
{
    
    /**
     * CommandManager construct.
     */
    public function __construct()
    {
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new ListCommand());
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new ECCommand());
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new BalanceCommand());
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new FixCommand());
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new GodCommand());
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new PvPCommand());
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new LogoutCommand());
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new NearCommand());
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new RenameCommand());
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new AutoFeedCommand());
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new TLCommand());
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new PayCommand());
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new FeedCommand());
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new LeaderboardsCommands());
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new ClearEntitiesCommand());
    }
}