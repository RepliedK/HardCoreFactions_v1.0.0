<?php

declare(strict_types=1);

namespace hcf\faction\command\subcommand;

use hcf\faction\command\FactionSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;

class MapSubCommand implements FactionSubCommand
{

    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player)
            return;

        if ($sender->getSession()->getFaction() === null) {
            $sender->sendMessage(TextFormat::colorize('&cYou don\'t have a faction'));
            return;
        }

        $faction = $sender->getSession()->getFaction();
        $factionpura = HCFLoader::getInstance()->getFactionManager()->getFaction($faction);
        $claim = HCFLoader::getInstance()->getClaimManager()->getClaim($factionpura->getName());
        if ($claim === null){
            $sender->sendMessage(TextFormat::colorize('&cYou dont have a claim'));
            return;
        }
        $x = $claim->getMaxX();
        $z = $claim->getMaxZ();
        if ($x === null || $z === null){
            $sender->sendMessage(TextFormat::colorize('&cYou dont have a claim'));
            return;
        }
        for ($y = $sender->getPosition()->getFloorY(); $y <= 127; $y++) {
            $sender->getNetworkSession()->sendDataPacket($this->sendFakeBlock(new Position($x, $y, $z, $sender->getWorld()), $y % 3 === 0 ? VanillaBlocks::GOLD() : VanillaBlocks::GLASS()));
        }
        $x = $claim->getMinX();
        $z = $claim->getMinZ();
        for ($y = $sender->getPosition()->getFloorY(); $y <= 127; $y++) {
            $sender->getNetworkSession()->sendDataPacket($this->sendFakeBlock(new Position($x, $y, $z, $sender->getWorld()), $y % 3 === 0 ? VanillaBlocks::GOLD() : VanillaBlocks::GLASS()));
        }
        $x = $claim->getMinX();
        $z = $claim->getMaxZ();
        for ($y = $sender->getPosition()->getFloorY(); $y <= 127; $y++) {
            $sender->getNetworkSession()->sendDataPacket($this->sendFakeBlock(new Position($x, $y, $z, $sender->getWorld()), $y % 3 === 0 ? VanillaBlocks::GOLD() : VanillaBlocks::GLASS()));
        }
        $x = $claim->getMaxX();
        $z = $claim->getMinZ();
        for ($y = $sender->getPosition()->getFloorY(); $y <= 127; $y++) {
            $sender->getNetworkSession()->sendDataPacket($this->sendFakeBlock(new Position($x, $y, $z, $sender->getWorld()), $y % 3 === 0 ? VanillaBlocks::GOLD() : VanillaBlocks::GLASS()));
        }

    }
    /**
     * @param Block $block
     * @return UpdateBlockPacket
     */
    private function sendFakeBlock(Position $position, Block $block): UpdateBlockPacket
    {
        $pos = BlockPosition::fromVector3($position->asVector3());
        $id = TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId($block->getStateId());
        $pk = UpdateBlockPacket::create($pos, $id, UpdateBlockPacket::FLAG_NETWORK, UpdateBlockPacket::DATA_LAYER_NORMAL);
        return $pk;
    }
}