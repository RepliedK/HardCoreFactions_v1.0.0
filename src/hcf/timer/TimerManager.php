<?php

declare(strict_types=1);

namespace hcf\timer;

use hcf\timer\command\EotwCommand;
use hcf\timer\command\PurgeCommand;
use hcf\timer\command\SotwCommand;
use hcf\timer\types\TimerEotw;
use hcf\timer\types\TimerPurge;
use hcf\timer\types\TimerSotw;
use hcf\HCFLoader;
use hcf\timer\command\CustomTimerCommand;
use hcf\timer\types\TimerCustom;

/**
 * Class TimerManager
 * @package hcf\timer
 */
class TimerManager
{
    
    private TimerSotw $sotw;
    
    private TimerEotw $eotw;

    private TimerPurge $purge;

    private array $customTimers = [];
    
    /**
     * TimerManager construct.
     */
    public function __construct()
    {
        # Setup main events
        $this->sotw = new TimerSotw;
        $this->eotw = new TimerEotw;
        $this->purge = new TimerPurge;
        # Register commands
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new EotwCommand());
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new SotwCommand());
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new PurgeCommand());
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new CustomTimerCommand());
        HCFLoader::getInstance()->getServer()->getPluginManager()->registerEvents(new TimerListener(), HCFLoader::getInstance());
    }

    public function update(): void {
        $this->getSotw()->update();
        $this->getEotw()->update();
        $this->getPurge()->update();
        foreach($this->getCustomTimers() as $name => $timer){
            if($timer instanceof TimerCustom)
                $timer->update();
        }
    }
    
    /**
     * @return TimerSotw
     */
    public function getSotw(): TimerSotw
    {
        return $this->sotw;
    }
    
    /**
     * @return TimerEotw
     */
    public function getEotw(): TimerEotw
    {
        return $this->eotw;
    }

    public function getPurge(): TimerPurge
    {
        return $this->purge;
    }

    public function getCustomTimers(): array {
        return $this->customTimers;
    }

    public function getCustomTimerByName(string $name): TimerCustom {
        return $this->customTimers[$name];
    }

    public function addCustomTimer(string $name): void {
        $this->customTimers[$name] = new TimerCustom($name);
    }

    public function removeCustomTimer(string $name): void {
        unset($this->customTimers[$name]);
    }

    public function hasCustomTimer(string $name): bool {
        return isset($this->customTimers[$name]);
    }

}