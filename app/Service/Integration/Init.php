<?php

namespace Wilcity_To_Directorist_Migrator\Service\Integration;

use Wilcity_To_Directorist_Migrator\Helper;

class Init {
    
    public function __construct() {
        // Register Controllers
        $controllers = $this->get_controllers();
        Helper\Serve::register_services( $controllers );
    }

    /**
     * Get Controllers
     * 
     * @return array $controllers
     */
    protected function get_controllers() {
        return [
            Wilcity\Init::class,
        ];
    }

}