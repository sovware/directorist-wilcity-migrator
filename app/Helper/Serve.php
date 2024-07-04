<?php

namespace Wilcity_To_Directorist_Migrator\Helper;

class Serve {

    public static function register_services( array $services = [] ) {

        foreach( $services as $service ) {

            if ( ! class_exists( $service ) ) {

                // var_dump( $service );
                // echo '<br>';
                // die( __FILE__ );

                continue;
            }

            new $service();

        }
        
    }

}