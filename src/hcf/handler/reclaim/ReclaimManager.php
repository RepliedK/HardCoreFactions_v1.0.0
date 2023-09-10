<?php

declare(strict_types=1);

namespace hcf\handler\reclaim;

use hcf\HCFLoader;
use hcf\handler\reclaim\command\ReclaimCommand;

use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;

/**
 * Class ReclaimManager
 * @package hcf\handler\reclaim
 */
class ReclaimManager
{
    
    /** @var Reclaim[] */
    private array $reclaims = [];
    
    /**
     * ReclaimManager construct.
     */
    public function __construct()
    {
        # Register reclaims
        foreach (HCFLoader::getInstance()->getProvider()->getReclaims() as $name => $data) {
            if ($data['permission'] !== null) {
                $this->registerPermission($data['permission']);
            }
            $this->createReclaim($name, $data['permission'], (int) $data['time'], $data['contents']);
        }
        # Register command
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new ReclaimCommand());
    }
    
    /**
     * @param string $permission
     */
    public function registerPermission(string $permission): void
    {
        $manager = PermissionManager::getInstance();
        $manager->addPermission(new Permission($permission));
        $manager->getPermission(DefaultPermissions::ROOT_OPERATOR)->addChild($permission, true);
    }
    
    /**
     * @return Reclaim[]
     */
    public function getReclaims(): array
    {
        return $this->reclaims;
    }
    
    /**
     * @param string $name
     * @return Reclaim|null
     */
    public function getReclaim(string $name): ?Reclaim
    {
        return $this->reclaims[$name] ?? null;
    }
    
    /**
     * @param string $name
     * @param string $permission
     * @param int $time
     * @param Item[] $contents
     */
    public function createReclaim(string $name, string $permission, int $time, array $contents = []): void
    {
        $this->reclaims[$name] = new Reclaim($name, $permission, $time, $contents);
    }
    
    /**
     * @param string $name
     */
    public function removeReclaim(string $name): void
    {
        unset($this->reclaims[$name]);
    }
}