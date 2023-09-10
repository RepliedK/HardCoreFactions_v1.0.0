<?php

namespace hcf\addons;

use hcf\addons\compass\CompassManager;
use hcf\addons\announcement\AnnouncementManager;

class AddonsManager {

    public CompassManager $compassManager;
    public AnnouncementManager $announcementManager;

    public function __construct(){
        $this->announcementManager = new AnnouncementManager;
        $this->compassManager = new CompassManager;
    }

    public function getAnnouncementManager(): AnnouncementManager {
        return $this->announcementManager;
    }

    public function getCompassManager(): CompassManager {
        return $this->compassManager;
    }

}