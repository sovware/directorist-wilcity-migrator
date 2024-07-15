<?php

namespace Wilcity_To_Directorist_Migrator\Controller\Hook;

use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\PlanHelper;


class Listings_Importer_Template_Override {

    /**
     * Constructor
     * 
     * @return void
     */

     public $importable_fields = [];

    public function __construct() {

        add_filter( 'directorist_admin_template', [ $this, 'listings_importer_body_template_step_2' ], 10, 3 );
        add_filter( 'directorist_admin_template_args', [ $this, 'directorist_admin_template_args' ], 10, 2 );
        add_filter( 'directorist_import_template_data', [ $this, 'directorist_admin_template_args' ], 10, 2 );

        add_action( 'init', [$this, 'setup_fields'] );


    }


    /**
         * Get Header Nav Menu
         *
         * @return array
         */
        public function get_header_nav_menu() {
            $step = isset( $_GET['step'] ) ? sanitize_key( wp_unslash( $_GET['step'] ) ) : '';
            $nav_menu = [];

            // Item - 1
            $nav_item                   = [];
            $nav_item['nav_item_class'] = ! $step ? esc_attr('active') : ( $step > 1 ? esc_attr('done') : '');
            $nav_item['label']          = esc_html__('Upload CSV File', 'directorist');
            $nav_menu[]                 = $nav_item;

            // Item - 2
            $nav_item                   = [];
            $class                      = ( '2' == $step ) ? esc_attr('active') : ( $step > 2 ? esc_attr('done') : '' );
            $class                      = 'atbdp-mapping-step ' . $class;
            $nav_item['nav_item_class'] = trim( $class );
            $nav_item['label']          = esc_html__('Column Mapping', 'directorist');
            $nav_menu[]                 = $nav_item;

            // Item - 3
            $nav_item                   = [];
            $class                      = ( $step == 3 ) ? esc_attr('done') : '';
            $class                      = 'atbdp-progress-step ' . $class;
            $nav_item['nav_item_class'] = trim( $class );
            $nav_item['label']          = esc_html__('Import', 'directorist');
            $nav_menu[]                 = $nav_item;

            // Item - 4
            $nav_item                   = [];
            $nav_item['nav_item_class'] = ( '3' == $step ) ? esc_attr('active done') : '';
            $nav_item['label']          = esc_html__('Done', 'directorist');
            $nav_menu[]                 = $nav_item;

            $nav_menu = apply_filters( 'directorist_listings_importer_header_nav_menu', $nav_menu, $step );

            return $nav_menu;
        }

        /**
         * Importer Header Template
         *
         * @param bool $return
         * @return string $template
         */
        public function importer_header_template( $return = false ) {
            $template_data = [];

            $template_data['controller']    = $this;
            $template_data['download_link'] = esc_url( ATBDP_URL .'views/admin-templates/import-export/data/dummy.csv' );
            $template_data['nav_menu']      = $this->get_header_nav_menu();

            $template_path = 'admin-templates/import-export/header-templates/header';
            ATBDP()->load_template( $template_path, $template_data );

        }

         /**
         * Importer header nav menu item template
         *
         * @param bool $return
         * @return string $template
         */
        public function importer_header_nav_menu_item_template( $template_data = [], $return = false ) {

            $template_data['controller'] = $this;
            ATBDP()->load_template( 'admin-templates/import-export/header-templates/nav-item', $template_data );

        }

         /**
         * Importer Body Template
         *
         * @param bool $return
         * @return string $template
         */
        public function importer_body_template( $return = false ) {
            $step = ( isset( $_REQUEST['step'] ) ) ? directorist_clean( wp_unslash( $_REQUEST['step'] ) ) : 1;
            $step = ( ! empty( $step ) && is_numeric( $step ) ) ? ( int ) $step : 1;
            $template_base_path = 'admin-templates/import-export/body-templates';
            $template_paths = [
                1 => "{$template_base_path}/step-one",
                2 => "{$template_base_path}/step-two",
                3 => "{$template_base_path}/step-done",
            ];

            $template_path = ( isset( $template_paths[ $step ] ) ) ? $template_paths[ $step ] : $template_paths[ 1 ];

            $template_data = [
                'controller' => $this,
                'step'       => $step,
            ];

            ATBDP()->load_template( $template_path, $template_data );

        }

    public function directorist_admin_template_args( $args, $template_name ) {

        if( 'admin-templates/import-export/body-templates/step-two' !== $template_name ) {
            return $args;
        }

       $current_listing_import_source = $this->get_current_listing_import_source();

       $total_listings = apply_filters( 'directorist_migrator_total_importing_listings', 0, $current_listing_import_source );

       $listings_data_map = apply_filters( 'directorist_migrator_importing_listings_data_map', [
           'headers' => [],
           'fields'  => $args['controller']->importable_fields,
       ], $current_listing_import_source );

       if ( empty( $listings_data_map['headers'] ) || ! is_array( $listings_data_map['headers'] ) ) {
           $listings_data_map['headers'] = [];
       }

       if ( empty( $listings_data_map['fields'] ) || ! is_array( $listings_data_map['fields'] ) ) {
           $listings_data_map['fields'] = [];
       }

       $template_data['controller']         = $this;
       $template_data['fields']             =  $this->importable_fields;
       $template_data['total_listings']     = $total_listings;
       $template_data['documentation_link'] = 'https://directorist.com/documentation/extensions';
       $template_data['listings_data_map']  = $listings_data_map;

       $template_data['data']  = $template_data;

       return $template_data;

    }

    /**
     * Listings Importer Body Template ( Step 2 )
     * 
     * @param string $content
     * @param int $step
     * 
     * @return string $content
     */
    public function listings_importer_body_template_step_2( $path, $template_name, $args ) {

       if( 'admin-templates/import-export/body-templates/step-two' !== $template_name ) {
            return $path;
       }

        $current_listing_import_source_type = $this->get_current_listing_import_source_type();

        if ( 'other' !== $current_listing_import_source_type ) {
            return $path;
        }

        $current_listing_import_source = $this->get_current_listing_import_source();

        $total_listings = apply_filters( 'directorist_migrator_total_importing_listings', 0, $current_listing_import_source );

        $listings_data_map = apply_filters( 'directorist_migrator_importing_listings_data_map', [
            'headers' => [],
            'fields'  => $args['controller']->importable_fields,
        ], $current_listing_import_source );

        if ( empty( $listings_data_map['headers'] ) || ! is_array( $listings_data_map['headers'] ) ) {
            $listings_data_map['headers'] = [];
        }

        if ( empty( $listings_data_map['fields'] ) || ! is_array( $listings_data_map['fields'] ) ) {
            $listings_data_map['fields'] = [];
        }

        $template_data['controller']         = $this;
        $template_data['total_listings']     = $total_listings;
        $template_data['documentation_link'] = 'https://directorist.com/documentation/extensions';
        $template_data['listings_data_map']  = $listings_data_map;

        $template_data['data']  = $template_data;

        // return connections_to_directorist_migrator_get_view( 'listings-importer/body-templates/step-2', $template_data, false );
        return connections_to_directorist_migrator_get_the_view_path( 'listings-importer/body-templates/step-2', $template_data, false );
    }

    public function setup_fields( $directory = '' ) {
        $directory = $directory ? $directory : directorist_default_directory();
        $fields    = directorist_get_form_fields_by_directory_type( 'id', $directory );

        $this->importable_fields[ 'publish_date' ]   = esc_html__( 'Publish Date', 'directorist' );
        $this->importable_fields[ 'listing_status' ] = esc_html__( 'Listing Status', 'directorist' );

        if ( empty( $fields ) || ! is_array( $fields ) ) {
            return;
        }

        foreach( $fields as $field ) {
            $field_key  = !empty( $field['field_key'] ) ? $field['field_key'] : '';
            $label      = !empty( $field['label'] ) ? $field['label'] : '';
            if( 'tax_input[at_biz_dir-location][]'  == $field_key ) {  $field_key = 'location'; }
            if( 'admin_category_select[]'           == $field_key ) {  $field_key = 'category';  }
            if( 'tax_input[at_biz_dir-tags][]'      == $field_key ) { $field_key = 'tag'; }

            if ( isset( $field['widget_name'] ) ) {
                if( 'pricing' == $field['widget_name'] ) {
                    $this->importable_fields[ 'price' ] = esc_html__( 'Price', 'directorist' );
                    $this->importable_fields[ 'price_range' ] = esc_html__( 'Price Range', 'directorist' );
                    continue;
                    }
                if( 'map' == $field['widget_name'] ) {
                    $this->importable_fields[ 'manual_lat' ] = esc_html__( 'Map Latitude', 'directorist' );
                    $this->importable_fields[ 'manual_lng' ] = esc_html__( 'Map Longitude', 'directorist' );
                    $this->importable_fields[ 'hide_map' ]   = esc_html__( 'Hide Map', 'directorist' );
                    continue;
                }
            }

            $this->importable_fields[ $field_key ] = $label;
        }
    }

    /**
     * Listings Importer Listings Data Map Table Template
     * 
     * @param array $template_data
     * @param bool $return
     * 
     * @return string $tempate
     */
    public function listings_importer_listings_data_map_table_template( $template_data = [] ) {

        $template_data['controller'] = $this;

        $aCustomPostTypes = GetSettings::getOptions(wilokeListingToolsRepository()->get('addlisting:customPostTypesKey'));

        if( ! $aCustomPostTypes ) {
            return '';
        }

        $post_type = $aCustomPostTypes[0]['slug'];

        $availableKey = General::getUsedSectionKey($post_type);
        // $availableKey = General::getUsedSectionKey('event');
        $aAvailablePlans = GetSettings::getOptions($availableKey, false, true);


        $result = [];

        foreach ($aAvailablePlans as $item) {
            if (isset($item['fieldGroups'])) {
                foreach ($item['fieldGroups'] as $key => $field) {
                    $result[] = [
                        'key' => $field['key'],
                        'label' => isset( $field['label'] ) ? $field['label'] : ''
                    ];
                }
            }
        }

        $template_data['headers'] = $result;
        $template_data['fields']  = $this->importable_fields;


        connections_to_directorist_migrator_get_the_view( 'listings-importer/tables/listings-data-map-table', $template_data, false );

    }

    /**
     * Get Current Listing Import Source Type
     * 
     * @return string
     */
    public function get_current_listing_import_source_type() {
        return isset( $_REQUEST['listing-import-source-type'] ) ? sanitize_text_field( $_REQUEST['listing-import-source-type'] ) : 'csv-file';
    }

    public function get_importable_fields() {
        return apply_filters( 'directorist_importable_fields', $this->importable_fields );
    }

    /**
     * Get Current Listing Import Source
     * 
     * @return string
     */
    public function get_current_listing_import_source() {
        return isset( $_REQUEST['listing-import-source'] ) ? sanitize_text_field( $_REQUEST['listing-import-source'] ) : '';
    }

}