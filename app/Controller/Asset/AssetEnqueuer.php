<?php

namespace Wilcity_To_Directorist_Migrator\Controller\Asset;

use Wilcity_To_Directorist_Migrator\Utility\Enqueuer;

abstract class AssetEnqueuer extends Enqueuer {

	public $asset_group = 'public';

	/**
	 * Load Scripts
	 *
	 * @return void
	 */
	abstract public function load_scripts();

	/**
	 * Enqueue Scripts
	 *
	 * @return void
	 */
	public function enqueue_scripts( $page = '' ) {

		// Set Script Version
		$this->setup_load_min_files();

		// Set Script Version
		$this->setup_script_version();

		// Load Script
		$this->load_scripts();

		// Apply Hook to Scripts
		$this->apply_hook_to_scripts();

		// CSS
		$this->register_css_scripts();
		$this->enqueue_css_scripts_by_group( [ 'group' => $this->asset_group, 'page' => $page ] );

		// JS
		$this->register_js_scripts();
		$this->enqueue_js_scripts_by_group( [ 'group' => $this->asset_group, 'page' => $page ] );

		// wp_enqueue_script( 'wilcity_main_migration', CONNECTIONS_TO_DIRECTORIST_MIGRATOR_JS_PATH . 'listings-importer.js', [], time(), [] );
	}

	/**
	 * Load min files
	 *
	 * @return void
	 */
	public function setup_load_min_files() {
		$this->load_min = apply_filters( 'CONNECTIONS_TO_DIRECTORIST_MIGRATOR_LOAD_MIN_FILE',  CONNECTIONS_TO_DIRECTORIST_MIGRATOR_LOAD_MIN_FILES );
	}

	/**
	 * Set Script Version
	 *
	 * @return void
	 */
	public function setup_script_version() {
		$script_version = ( $this->load_min ) ? CONNECTIONS_TO_DIRECTORIST_MIGRATOR_SCRIPT_VERSION : md5( time() );
		$this->script_version = apply_filters( 'CONNECTIONS_TO_DIRECTORIST_MIGRATOR_SCRIPT_VERSION', $script_version );
	}

	/**
	 * Apply Hook to Scripts
	 *
	 * @return void
	 */
	public function apply_hook_to_scripts() {
		$this->css_scripts = apply_filters( 'CONNECTIONS_TO_DIRECTORIST_MIGRATOR_CSS_SCRIPTS', $this->css_scripts );
		$this->js_scripts = apply_filters( 'CONNECTIONS_TO_DIRECTORIST_MIGRATOR_JS_SCRIPTS', $this->js_scripts );
	}
}