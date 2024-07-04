<?php

namespace Connections_To_Directorist_Migrator\Service\Integration\Connections\Hook;

use Connections_To_Directorist_Migrator\Helper\Links;

class Add_Migration_Links {

    /**
     * Constructor
     * 
     * @return void
     */
    public function __construct() {

        add_filter( 'plugin_action_links', [ $this, 'add_migration_link_to_plugin_action' ], 20, 4 );
        add_action( 'admin_head-edit.php', [ $this, 'add_migration_link_to_all_listings_page' ], 20, 1 );
        
    }

    /**
     * Add Migration Link To All Listings Page
     * 
     * @return void
     */
    public function add_migration_link_to_all_listings_page() {

        global $post_type_object;

        if ( $post_type_object->name !== ATBDP_POST_TYPE ) {
            return;
        }

        $script_id = 'wilcity-to-directorist-migrator-service-integration-wilcity-all-listings-page';
        wp_enqueue_script( $script_id );
        wp_localize_script( $script_id, 'script_data', self::get_all_listings_page_script_data() );
    }

    /**
     * Get All Listings Page Script Data
     * 
     * @return array Script Data
     */
    public static function get_all_listings_page_script_data() {
        $actions = [
            'migrate-from-wilcity' => [
                'label' => __( 'Migrate from Connections', 'wilcity-to-directorist-migrator' ),
                'link'  => Links::get_listings_migration_mapping_page_url(),
            ],
        ];

        $script_data = [];
        $script_data[ 'pageTitleActions' ] = $actions;

        return $script_data;
    }

    /**
     * Add Migration Link To Plugin Action
     * 
     * @param $fields Fields
     * @return array Fields
     */
    public function add_migration_link_to_plugin_action( $actions, $plugin_file, $plugin_data, $context ) {

        if ( 'wilcity-to-directorist-migrator/wilcity-to-directorist-migrator.php' !== $plugin_file ) {
            return $actions;
        }

        $link  = Links::get_listings_migration_mapping_page_url();
        $label = __( 'Start Migrating', 'wilcity-to-directorist-migrator' );

        $actions['start-migrating'] = '<a href="'. $link .'">'. $label .'</a>';
        

        return $actions;
    }

}