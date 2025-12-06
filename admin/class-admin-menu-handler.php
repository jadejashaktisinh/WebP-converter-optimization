<?php

class AdminMenuHandler {

	/**
	 * Adds the admin menu item in WordPress.
	 */
	function webp_opt_admin_menu() {
		add_menu_page(
			'WebP Optimizer Settings',
			'WebP Optimizer',
			'manage_options',
			'webp-optimizer-settings',
			array($this,'webp_opt_render_admin_page'),
			'dashicons-format-image',
			100
		);
	}

	/**
	 * Renders the HTML container for the React app.
	 */
	function webp_opt_render_admin_page() {
		echo '<div class="wrap">';
		echo '<div id="webp-optimizer-admin-root"></div>';
		echo '</div>';
	}

}
