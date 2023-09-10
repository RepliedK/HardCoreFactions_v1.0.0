<?php

namespace hcf\handler;

use hcf\handler\crate\CrateManager;
use hcf\handler\kit\KitManager;
use hcf\handler\reclaim\ReclaimManager;

class HandlerManager {

    public CrateManager $crateManager;
    public KitManager $kitManager;
    public ReclaimManager $reclaimManager;

    public function __construct(){
        $this->crateManager = new CrateManager;
        $this->kitManager = new KitManager;
        $this->reclaimManager = new ReclaimManager;
    }

    public function getCrateManager(): CrateManager {
        return $this->crateManager;
    }

    public function getKitManager(): KitManager {
        return $this->kitManager;
    }

    public function getReclaimManager(): ReclaimManager {
        return $this->reclaimManager;
    }
    
}