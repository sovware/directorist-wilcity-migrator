<?php

namespace Wilcity_To_Directorist_Migrator\Service\Integration\Wilcity;

use Wilcity_To_Directorist_Migrator\Helper;
use Wilcity_To_Directorist_Migrator\Service\Integration\Wilcity;
use WilokeListingTools\Framework\Helpers\GetSettings;
use \Directorist\Multi_Directory\Multi_Directory_Manager;

class Init {
    
    public function __construct() {

        add_action( 'admin_init', [ $this, 'setup' ] );

        add_action( 'admin_init', [ $this, 'init_listing_type_migration' ] );

    }

    public function init_listing_type_migration() {

        if( get_option( 'directorist_wilcity_type_migrated' ) ) {
            return;
        }
        
        if ( ! is_plugin_active( 'directorist/directorist-base.php' ) ) {
            return;
        }

        $aCustomPostTypes = GetSettings::getOptions(wilokeListingToolsRepository()->get('addlisting:customPostTypesKey'));
        
        if( ! $aCustomPostTypes ) {
            return;
        }

        $file = DIRECTORIST_ASSETS_DIR . 'sample-data/directory/directory.json';
        
        if ( ! file_exists( $file ) ) { return; }
        
        $file_contents = file_get_contents( $file );

        if ( ! function_exists( 'ATBDP' ) ) { return; }

        $multi_directory_manager = ATBDP()->multi_directory_manager;
        $multi_directory_manager->prepare_settings();

        foreach( $aCustomPostTypes as $post_type ) {
            $directory = $multi_directory_manager->add_directory([
                'directory_name' => $post_type['name'],
                'fields_value'   => $file_contents,
                'is_json'        => true
            ]);

            if( is_wp_error( $directory ) ) {
                continue;
            }

            update_term_meta( $directory['term_id'], 'migrated_from', $post_type['slug'] );
            if( 'Listing' === $post_type['name'] ) {
                update_term_meta( $directory['term_id'], '_default', true );
            }
        }

        $options = get_option('atbdp_option');

        $options['enable_multi_directory'] = true;

        update_option('atbdp_option', $options);

        update_option( 'directorist_wilcity_type_migrated', 1 );

    }

    /**
     * Setup
     */
    public function setup() {  
     
        if ( ! is_plugin_active( 'wiloke-listing-tools/wiloke-listing-tools.php' ) ) {
            return;
        }

        $this->Init();
    }

    /**
     * Init
     * 
     * @return void
     */
    public function Init() {

        // Define Const
        $this->define_const();

        // Includes
        $this->includes();

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
            Wilcity\Hook\Init::class,
        ];
    }

    /**
     * Define Const
     * 
     * @return void
     */
    protected function define_const() {
        
        defined( 'DIRECTORIST_MIGRATOR_INTEGRATION_CONNECTIONS_ID' ) || define( 'DIRECTORIST_MIGRATOR_INTEGRATION_CONNECTIONS_ID', 'wilcity' );
        
    }

    /**
     * Includes
     * 
     * @return void
     */
    protected function includes() {

        $dir_path = dirname( __FILE__ ) . '/functions';
        
        connections_to_directorist_migrator_include_dir_files( $dir_path );
    }

}