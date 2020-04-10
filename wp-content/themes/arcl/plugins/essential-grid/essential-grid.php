<?php
/* Essential Grid support functions
------------------------------------------------------------------------------- */


// Theme init priorities:
// 9 - register other filters (for installer, etc.)
if (!function_exists('lighthouseschool_essential_grid_theme_setup9')) {
	add_action( 'after_setup_theme', 'lighthouseschool_essential_grid_theme_setup9', 9 );
	function lighthouseschool_essential_grid_theme_setup9() {
		if (lighthouseschool_exists_essential_grid()) {
			add_action( 'wp_enqueue_scripts', 							'lighthouseschool_essential_grid_frontend_scripts', 1100 );
			add_filter( 'lighthouseschool_filter_merge_styles',					'lighthouseschool_essential_grid_merge_styles' );
		}
		if (is_admin()) {
			add_filter( 'lighthouseschool_filter_tgmpa_required_plugins',		'lighthouseschool_essential_grid_tgmpa_required_plugins' );
		}
	}
}

// Filter to add in the required plugins list
if ( !function_exists( 'lighthouseschool_essential_grid_tgmpa_required_plugins' ) ) {
	//Handler of the add_filter('lighthouseschool_filter_tgmpa_required_plugins',	'lighthouseschool_essential_grid_tgmpa_required_plugins');
	function lighthouseschool_essential_grid_tgmpa_required_plugins($list=array()) {
		if (lighthouseschool_storage_isset('required_plugins', 'essential-grid')) {
			$path = lighthouseschool_get_file_dir('plugins/essential-grid/essential-grid.zip');
			if (!empty($path) || lighthouseschool_get_theme_setting('tgmpa_upload')) {
				$list[] = array(
						'name' 		=> lighthouseschool_storage_get_array('required_plugins', 'essential-grid'),
						'slug' 		=> 'essential-grid',
						'version'	=> '2.3.3',
						'source'	=> !empty($path) ? $path : 'upload://essential-grid.zip',
						'required' 	=> false
				);
			}
		}
		return $list;
	}
}

// Check if plugin installed and activated
if ( !function_exists( 'lighthouseschool_exists_essential_grid' ) ) {
	function lighthouseschool_exists_essential_grid() {
		return defined('EG_PLUGIN_PATH');
	}
}
	
// Enqueue plugin's custom styles
if ( !function_exists( 'lighthouseschool_essential_grid_frontend_scripts' ) ) {
	function lighthouseschool_essential_grid_frontend_scripts() {
		if (lighthouseschool_is_on(lighthouseschool_get_theme_option('debug_mode')) && lighthouseschool_get_file_dir('plugins/essential-grid/essential-grid.css')!='')
			wp_enqueue_style( 'lighthouseschool-essential-grid',  lighthouseschool_get_file_url('plugins/essential-grid/essential-grid.css'), array(), null );
	}
}
	
// Merge custom styles
if ( !function_exists( 'lighthouseschool_essential_grid_merge_styles' ) ) {
	function lighthouseschool_essential_grid_merge_styles($list) {
		$list[] = 'plugins/essential-grid/essential-grid.css';
		return $list;
	}
}
?>