<?php

namespace Mercator\Redirect;

use Mercator\Mapping;

add_action( 'plugins_loaded', __NAMESPACE__ . '\handle_redirect' );

function is_enabled( $mapping = null ) {
	$mapping = $mapping ?: $GLOBALS['mercator_current_mapping'];

	/**
	 * Determine whether a mapping should be used
	 *
	 * Typically, you'll want to only allow active mappings to be used. However,
	 * if you want to use more advanced logic, or allow non-active domains to
	 * be mapped too, simply filter here.
	 *
	 * @param boolean $is_active Should the mapping be treated as active?
	 * @param Mapping $mapping Mapping that we're inspecting
	 */
	return apply_filters( 'mercator.redirect.enabled', $mapping->is_active(), $mapping );
}

function redirect_admin() {
	/**
	 * Whether to redirect mapped domains on visits to the WP admin.
	 * Recommended to leave this false as there's no SEO problem
	 * and avoids having a broken / unreachable admin if the
	 * custom domain DNS is incorrect or not fully propagated.
	 *
	 * @param bool Set to true to enable admin redirects
	 */
	return apply_filters( 'mercator.redirect.admin.enabled', false );
}

function use_legacy_redirect() {
	/**
	 * If you still have blogs with the main domain as the subdomain
	 * or subsite path this will allow you to still redirect to the
	 * first active alias found.
	 *
	 * @param bool Set to true to enable legacy redirects
	 */
	return apply_filters( 'mercator.redirect.legacy.enabled', false );
}

/**
 * Performs the redirect to the primary domain
 */
function handle_redirect() {

	// Custom domain redirects need SUNRISE.
	if ( ! defined( 'SUNRISE' ) || ! SUNRISE ) {
		return;
	}

	// Should we redirect visits to the admin?
	if ( ( is_admin() || $GLOBALS['pagenow'] === 'wp-login.php' ) && ! redirect_admin() ) {
		return;
	}


	// Don't redirect REST API requests
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return;
	}

	// Disable redirect on WP CLI.
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return;
	}

	// Disable for XML RPC.
	if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
		return;
	}

	// Disable redirects for crons.
	if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
		return;
	}

	// Disable redirects for admin ajax.
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return;
	}

	// Support for alias as primary domain
	if ( use_legacy_redirect() ) {
		legacy_redirect();
		return;
	}

	// Get mapping if it exists, if we're already on the primary domain we'll exit here
	$mapping = Mapping::get_by_domain( $_SERVER['HTTP_HOST'] );
	if ( is_wp_error( $mapping ) || ! $mapping ) {
		return;
	}

	if ( ! is_enabled( $mapping ) ) {
		return;
	}

	// If current domain and domain mapping are the same, exit early.
	$site   = $mapping->get_site();
	$domain = $site->domain;
	if ( $domain === $_SERVER['HTTP_HOST'] ) {
		return;
	}
	$path = $site->path;
	// Use blogs table domain as the primary domain
	redirect( $domain . $path );
}

/**
 * Check if the main site domain contains the network hostname
 * and use the first active alias if so
 */
function legacy_redirect() {
	$site = get_site( get_current_blog_id() );

	// Check the blog domain isn't a subdomain or subfolder
	if ( false === strpos( $site->domain, get_current_site()->domain ) ) {
		if ( $_SERVER['HTTP_HOST'] !== $site->domain ) {
			redirect( $site->domain . $site->path );
		}

		return;
	}

	$mappings = Mapping::get_by_site( get_current_blog_id() );
	if ( is_wp_error( $mappings ) || ! $mappings ) {
		return;
	}

	foreach ( $mappings as $mapping ) {
		if ( ! is_enabled( $mapping ) ) {
			continue;
		}

		// Redirect to the first active alias if we're not there already
		if ( $_SERVER['HTTP_HOST'] !== $mapping->get_domain() ) {
			$domain = $mapping->get_domain();
			$path   = $mapping->get_site()->path;
			redirect( $domain . $path );
		} else {
			break;
		}
	}
}

/**
 * Helper function to redirect to url
 *
 * @param string $url
 */
function redirect( $url ) {
	$status_code = (int) apply_filters( 'mercator.redirect.status.code', 301 );
	$domain      = untrailingslashit( set_url_scheme( "http://{$url}" ) );
	wp_redirect( $domain . esc_url_raw( $_SERVER['REQUEST_URI'] ), $status_code );
	exit;
}
