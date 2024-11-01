<?php

// If plugin is not being uninstalled, exit (do nothing)
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

update_option( 'woo_maint_display_type', '' );
update_option( 'woo_maint_display_scope', '' );