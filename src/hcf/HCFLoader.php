<?php

declare(strict_types=1);

namespace hcf;

use CortexPE\DiscordWebhookAPI\Embed;
use CortexPE\DiscordWebhookAPI\Message;
use CortexPE\DiscordWebhookAPI\Webhook;
use hcf\addons\AddonsManager;
use hcf\entity\EntityManager;
use hcf\claim\ClaimManager;
use hcf\command\CommandManager;
use hcf\module\enchantment\EnchantmentManager;
use hcf\entity\custom\CustomItemEntity;
use hcf\timer\TimerManager;
use hcf\faction\FactionManager;
use hcf\koth\KothManager;
use hcf\player\disconnected\DisconnectedManager;
use hcf\session\SessionManager;
use hcf\database\DataProvider;
use muqsit\invmenu\InvMenuHandler;
use hcf\entity\custom\TextEntity;
use hcf\handler\HandlerManager;
use hcf\item\ItemManager;
use hcf\module\ModuleManager;
use hcf\timer\types\TimerCustom;
use hcf\utils\logic\time\Timer;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;

/**
 * Class HCFLoader
 * @package hcf
 */
class HCFLoader extends PluginBase
{
    
    /** @var HCFLoader */
    public static HCFLoader $instance;
    /** @var DataProvider */
    public DataProvider $database;
    /** @var EntityManager */
    public EntityManager $entityManager;
    /** @var ClaimManager */
    public ClaimManager $claimManager;
    /** @var CommandManager */
    public CommandManager $commandManager;
    /** @var EnchantmentManager */
    public EnchantmentManager $enchantmentManager;
    /** @var TimerManager */
    public TimerManager $TimerManager;
    /** @var FactionManager */
    public FactionManager $factionManager;
    /** @var KothManager */
    public KothManager $kothManager;
    /** @var DisconnectedManager */
    public DisconnectedManager $disconnectedManager;
    /** @var SessionManager */
    public SessionManager $sessionManager;
    /** @var ItemManager */
    public ItemManager $itemManager;
    /** @var ModuleManager */
    public ModuleManager $moduleManager;
    /** @var HandlerManager */
    public HandlerManager $handlerManager;
    public AddonsManager $addonsManager;
    
    protected function onLoad(): void
    {
        self::$instance = $this;
    }
    
    protected function onEnable() : void
    {

        if (!InvMenuHandler::isRegistered())
            InvMenuHandler::register($this);
        
        $this->database = new DataProvider;
        $this->moduleManager = new ModuleManager;
        $this->entityManager = new EntityManager;
        $this->claimManager = new ClaimManager;
        $this->commandManager = new CommandManager;
        $this->enchantmentManager = new EnchantmentManager;
        $this->TimerManager = new TimerManager;
        $this->factionManager = new FactionManager;
        $this->kothManager = new KothManager;
        $this->disconnectedManager = new DisconnectedManager;
        $this->sessionManager = new SessionManager;
        $this->itemManager = new ItemManager;
        $this->handlerManager = new HandlerManager;
        $this->addonsManager = new AddonsManager;
        
        # Register listener
        $this->getServer()->getPluginManager()->registerEvents(new HCFListener(), $this);

        # Motd
        $this->getServer()->getNetwork()->setName(TextFormat::colorize($this->getConfig()->get('motd')));
        
        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            # Koth
            if (($kothName = $this->getKothManager()->getKothActive()) !== null) {
                if (($koth = $this->getKothManager()->getKoth($kothName)) !== null)
                    $koth->update();
                else
                    $this->getKothManager()->setKothActive(null);
            }
            
            # Events
            $this->getTimerManager()->getSotw()->update();
            $this->getTimerManager()->getEotw()->update();
            $this->getTimerManager()->getPurge()->update();
            foreach($this->getTimerManager()->getCustomTimers() as $name => $timer){
                if($timer instanceof TimerCustom)
                    $timer->update();
            }
                
            # Sessions
            foreach ($this->getSessionManager()->getSessions() as $session)
                $session->onUpdate();
                
            # Factions
            foreach ($this->getFactionManager()->getFactions() as $faction)
                $faction->onUpdate();
        }), 20);
    }
    
    protected function onDisable(): void
    {
        $this->getProvider()->save();
        $this->disconnectedManager->onDisable();
        $this->getHandlerManager()->getCrateManager()->onDisable();
        
        $world = $this->getServer()->getWorldManager()->getDefaultWorld();
        foreach ($world->getEntities() as $entity) {
            if ($entity instanceof CustomItemEntity){
                if($entity instanceof TextEntity){
                    $entity->close();
                }
            }
        }
    }

    /**
     * @return HCFLoader
     */
    public static function getInstance(): HCFLoader
    {
        return self::$instance;
    }
    
    /**
     * @return DataProvider
     */
    public function getProvider(): DataProvider
    {
        return $this->database;
    }
    
    /**
     * @return EntityManager
     */
    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }
    
    /**
     * @return ClaimManager
     */
    public function getClaimManager(): ClaimManager
    {
        return $this->claimManager;
    }
    
    /**
     * @return CommandManager
     */
    public function getCommandManager(): CommandManager
    {
        return $this->commandManager;
    }
    
    /**
     * @return EnchantmentManager
     */
    public function getEnchantmentManager(): EnchantmentManager
    {
        return $this->enchantmentManager;
    }
    
    /**
     * @return TimerManager
     */
    public function getTimerManager(): TimerManager
    {
        return $this->TimerManager;
    }
    
    /**
     * @return FactionManager
     */
    public function getFactionManager(): FactionManager
    {
        return $this->factionManager;
    }
    
    /**
     * @return KothManager
     */
    public function getKothManager(): KothManager
    {
        return $this->kothManager;
    }
    
    /**
     * @return DisconnectedManager
     */
    public function getDisconnectedManager(): DisconnectedManager
    {
        return $this->disconnectedManager;
    }
    
    /**
     * @return SessionManager
     */
    public function getSessionManager(): SessionManager
    {
        return $this->sessionManager;
    }

    public function getItemManager(): ItemManager
    {
        return $this->itemManager;
    }
    
    public function getModuleManager(): ModuleManager 
    {
        return $this->moduleManager;
    }

    public function getHandlerManager(): HandlerManager {
        return $this->handlerManager;
    }

}