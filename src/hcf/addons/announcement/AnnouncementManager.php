<?php

namespace hcf\addons\announcement;

use hcf\HCFLoader;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;

class AnnouncementManager {

    private $messages;

    private $currentId = 0;

    public function __construct(){
        $this->init();
        HCFLoader::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            $message = $this->getNextMessage();
            HCFLoader::getInstance()->getServer()->broadcastMessage(TextFormat::colorize(HCFLoader::getInstance()->getConfig()->get('prefix').$message));
        }), 5 * 60 * 20);
    }

    public function init(): void {
        $this->messages = HCFLoader::getInstance()->getConfig()->get("messages");
    }

    /**
     * @return string
     */
    public function getNextMessage(): string {
        if(isset($this->messages[$this->currentId])) {
            $message = $this->messages[$this->currentId];
            $this->currentId++;
            return $message;
        }
        $this->currentId = 0;
        return $this->messages[$this->currentId];
    }
}