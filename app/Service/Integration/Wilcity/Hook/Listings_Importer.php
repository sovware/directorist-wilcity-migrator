<?php

namespace Wilcity_To_Directorist_Migrator\Service\Integration\Wilcity\Hook;

use Wilcity_To_Directorist_Migrator\Service\Integration\Wilcity\Helper\Listings_Data_Helper;
use Wilcity_To_Directorist_Migrator\Service\Integration\Wilcity\Model\Listings_Model;


class Listings_Importer {

    /**
     * Constructor
     * 
     * @return void
     */
    public function __construct() {
        add_filter( 'directorist_listings_importing_posts', [ $this, 'listings_importing_posts' ], 20, 4 );
    }


    /**
     * Listings Importing Posts
     * 
     * @param int $total_listings
     * @param string $current_listing_import_source
     * 
     * @return int Total Listings
     */
    public function listings_importing_posts( $posts, $position, $limit, $request ) {

        $current_listing_import_source = ( isset( $request['listing_import_source'] ) ) ? $request['listing_import_source'] : '';

        if ( DIRECTORIST_MIGRATOR_INTEGRATION_CONNECTIONS_ID !== $current_listing_import_source ) {
            return $posts;
        }
        $directory_type = isset( $request['directory_type'] ) ? $request['directory_type'] : '';
        $post_type = get_term_meta( $directory_type, 'migrated_from', true );
        $atts = [
            'posts_per_page'  => $limit,
            'paged' => $position,
            'post_type' => $post_type,
        ];

        $posts = Listings_Model::get_listings( $atts );

        wp_send_json([
            'error' => true,
            'posts' => $posts,
            'atts' => $atts,
            'request' => $request,
            'post_meta' => get_post_meta( $posts[0]->ID ),
        ]);

        if ( empty( $posts ) ) {
            return $posts;
        }

        $importable_posts = [];

        foreach( $posts as $post ) {
            $importable_posts[] = Listings_Data_Helper::get_importable_fields( $post );
        }

        return $importable_posts;
    }

}