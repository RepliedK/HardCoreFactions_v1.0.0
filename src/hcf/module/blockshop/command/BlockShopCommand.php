<?php

declare(strict_types=1);

namespace hcf\module\blockshop\command;

use hcf\module\blockshop\entity\BlockShopEntity;
use hcf\module\blockshop\entity\SellShopEntity;
use hcf\player\Player;
use hcf\module\blockshop\utils\Utils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class BlockShopCommand extends Command
{

    /**
     * BlockShopCommand construct.
     */
    public function __construct()
    {
        parent::__construct('blockshop', 'Command for blockshop');
        $this->setPermission('blockshop.command');
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$sender instanceof Player)
            return;

        if ($sender->getCurrentClaim() !== 'Spawn')
            return;

        if (isset($args[0]) && $sender->getServer()->isOp($sender->getName())) {
            if ($args[0] === 'npc') {
                if (isset($args[1])) {
                    if ($args[1] === 'buy') {
                        $entity = new BlockShopEntity($sender->getLocation(), $sender->getSkin(), Utils::createBasicNBT($sender));
                        $entity->spawnToAll();
                        return;
                    }
                    if ($args[1] === 'sell') {
                        $entity = new SellShopEntity($sender->getLocation(), $sender->getSkin(), Utils::createBasicNBT($sender));
                        $entity->spawnToAll();
                        return;
                    }
                    return;
                }
            }
        }
        Utils::openBlockShop($sender);
    }
}
