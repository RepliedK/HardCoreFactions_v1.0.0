<?php

namespace hcf\koth\task;

use CortexPE\DiscordWebhookAPI\Embed;
use CortexPE\DiscordWebhookAPI\Message;
use CortexPE\DiscordWebhookAPI\Webhook;
use hcf\HCFLoader;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;

class AutoKothTask extends Task {

    public function onRun(): void {
        foreach(HCFLoader::getInstance()->getKothManager()->getKoths() as $name => $data){
            if (HCFLoader::getInstance()->getKothManager()->getKothActive() === null) {
                $koth = HCFLoader::getInstance()->getKothManager()->getKoth($name);
                if ($koth->getName() !== "Citadel") {
                    HCFLoader::getInstance()->getKothManager()->setKothActive($name);
                    
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
            }
        }
    }

}