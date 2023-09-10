<?php

declare(strict_types=1);

namespace hcf\handler\crate\form;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Input;
use hcf\handler\crate\tile\CrateTile;
use hcf\HCFLoader;
use pocketmine\block\tile\Chest;
use pocketmine\block\VanillaBlocks;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;

/**
 * class CrateForm
 * @package hcf\utils
 */
final class CrateForm
{
    
    /**
     * @param Player $player
     * @param Position $position
     */
    public static function createCreateTile(Player $player, Position $position): void
    {
        $form = new CustomForm(TextFormat::colorize('&2Create crate tile'), [
            new Input('crate_name', TextFormat::colorize('&7Crate name'), 'Place the name of the crate you want to place on the tile')
       ],
       function(Player $submitter, CustomFormResponse $response) use ($position): void {
           $crateName = $response->getString('crate_name');
           $crate = HCFLoader::getInstance()->getHandlerManager()->getCrateManager()->getCrate($crateName);
           
           if ($crate === null) {
               $submitter->sendMessage(TextFormat::colorize('&cThe crate you want to place does not exist'));
               return;
           }
           $block = $position->getWorld()->getBlock($position->asVector3());
           $tile = $position->getWorld()->getTile($position->asVector3());
           
           if ($block->getTypeId() !== VanillaBlocks::CHEST()->getTypeId()) {
               $submitter->sendMessage(TextFormat::colorize('&cThe position that was saved there is no chest'));
               return;
           }
           
           if (!$tile instanceof Chest) {
               $submitter->sendMessage(TextFormat::colorize('&cThe saved position is not a chest tile'));
               return;
           }
           $tile->close();
           
           $newTile = new CrateTile($position->getWorld(), $position->asVector3());
           $newTile->setCrateName($crateName);
           $position->getWorld()->addTile($newTile);
           
           $submitter->sendMessage(TextFormat::colorize('&aThe tile of the crate ' . $crateName . ' was successfully added'));
        });
        $player->sendForm($form);
    }
}