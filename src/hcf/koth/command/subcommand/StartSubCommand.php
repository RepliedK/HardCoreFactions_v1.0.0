<?php

declare(strict_types=1);

namespace hcf\koth\command\subcommand;

use CortexPE\DiscordWebhookAPI\Embed;
use CortexPE\DiscordWebhookAPI\Message;
use CortexPE\DiscordWebhookAPI\Webhook;
use hcf\koth\command\KothSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

/**
 * Class StartSubCommand
 * @package hcf\koth\command\subcommand
 */
class StartSubCommand implements KothSubCommand
{
    
    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function execute(CommandSender $sender, array $args): void
    {
        if (count($args) < 1) {
            $sender->sendMessage(TextFormat::colorize('&c/koth start [string: name]'));
            return;
        }
        $name = $args[0];
        
        if (HCFLoader::getInstance()->getKothManager()->getKothActive() !== null) {
            $sender->sendMessage(TextFormat::colorize('&cThere is already activated a koth right now'));
            return;
        }
        
        if (HCFLoader::getInstance()->getKothManager()->getKoth($name) === null) {
            $sender->sendMessage(TextFormat::colorize('&cThe koth does not exist'));
            return;
        }
        $koth = HCFLoader::getInstance()->getKothManager()->getKoth($name);
        $location = HCFLoader::getInstance()->getKothManager()->getKoth($name)->getCoords();
        $time = HCFLoader::getInstance()->getKothManager()->getKoth($name)->getTime() / 60;
        $points = HCFLoader::getInstance()->getKothManager()->getKoth($name)->getPoints();
        
        if ($koth->getCapzone() === null) {
            $sender->sendMessage(TextFormat::colorize('&cThe capzone is not selected'));
            return;
        }

        if ($koth->getName() !== "Citadel") {
            HCFLoader::getInstance()->getKothManager()->setKothActive($name);
            $sender->sendMessage(TextFormat::colorize('&aYou have activated the koth ' . $name));

            $webHook = new Webhook(HCFLoader::getInstance()->getConfig()->get('koth.webhook'));

            HCFLoader::getInstance()->getServer()->broadcastMessage(TextFormat::colorize("&7███████"));
            HCFLoader::getInstance()->getServer()->broadcastMessage(TextFormat::colorize("&7█&3█&7███&3█&7█"));
            HCFLoader::getInstance()->getServer()->broadcastMessage(TextFormat::colorize("&7█&3█&7██&3█&7██ &r&7[&d&lLegacy KoTHs7]"));
            HCFLoader::getInstance()->getServer()->broadcastMessage(TextFormat::colorize("&7█&3███&7███ &r&9" . $koth->getName() . " &ehas been started &6" . $koth->getCoords() . "!"));
            HCFLoader::getInstance()->getServer()->broadcastMessage(TextFormat::colorize("&7█&3█&7██&3█&7██ &r&7[&d&lLegacy KoTHs&r&7] &ewin the event a get rewards"));
            HCFLoader::getInstance()->getServer()->broadcastMessage(TextFormat::colorize("&7█&3█&7███&3█&7█"));
            HCFLoader::getInstance()->getServer()->broadcastMessage(TextFormat::colorize("&7█&3█&7███&3█&7█"));
            HCFLoader::getInstance()->getServer()->broadcastMessage(TextFormat::colorize("&7███████"));


            $msg = new Message();
            $msg->setContent('<@&1140444369552425095>');
            $embed = new Embed();
            $embed->setTitle("KoTH Event - HCF");
            $embed->setColor(0xC13DFF);            
            $embed->setDescription("The **$name** KoTH has been actived!!\n 🧱 Cordinates: **500 90 500**\n ⏳ Capture Time: **05:00**\n\n> IP: **legacymc.ddns.net**\n> Port: **19139**\n> Store: **https://store.legacymc.cc**");
            $embed->setFooter("KoTH Active");          
            $msg->addEmbed($embed);


            $webHook->send($msg);
        }
        if ($koth->getName() === "Citadel") {
            HCFLoader::getInstance()->getKothManager()->setKothActive($name);
            $sender->sendMessage(TextFormat::colorize('&aYou have activated the koth ' . $name));

            $webHook = new Webhook(HCFLoader::getInstance()->getConfig()->get('koth.webhook'));

            HCFLoader::getInstance()->getServer()->broadcastMessage(TextFormat::colorize("&7███████"));
            HCFLoader::getInstance()->getServer()->broadcastMessage(TextFormat::colorize("&7██&5████&7█"));
            HCFLoader::getInstance()->getServer()->broadcastMessage(TextFormat::colorize("&7█&5█&7█████ &r&6[Citadel]"));
            HCFLoader::getInstance()->getServer()->broadcastMessage(TextFormat::colorize("&7█&5█&7█████ &r&ehas been started &6" . $koth->getCoords() . "!"));
            HCFLoader::getInstance()->getServer()->broadcastMessage(TextFormat::colorize("&7█&5█&7█████ &r&6&ewin the event a get rewards"));
            HCFLoader::getInstance()->getServer()->broadcastMessage(TextFormat::colorize("&7█&5█&7█████"));
            HCFLoader::getInstance()->getServer()->broadcastMessage(TextFormat::colorize("&7██&5████&7█"));
            HCFLoader::getInstance()->getServer()->broadcastMessage(TextFormat::colorize("&7███████"));


            $msg = new Message();

            $msg->setContent("@everyone");

            $embed = new Embed();
            $embed->setTitle("Citadel has started 🌌");
            $embed->setColor(0xC13DFF);
            $embed->addField("Location 📍", "{$location}");
            $embed->addField("Time 🕐", "{$time} minutes", true);
            $embed->addField("Rewards 🔑", "{$points} Points", true);
            $embed->setFooter("");
            $msg->addEmbed($embed);


            $webHook->send($msg);
        }
    }
}