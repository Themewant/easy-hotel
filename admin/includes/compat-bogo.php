<?php
/**
 * Bogo compatibility shim.
 *
 * Bogo's bogo_option_sticky_posts() (bogo/includes/query.php) hooks the
 * `option_sticky_posts` filter and calls is_home() with no guard. The
 * `option_sticky_posts` option is read whenever any query touches sticky
 * posts, which can happen before the main query is set up (e.g. while the
 * block editor is loading on post-new.php). Calling is_home() that early
 * triggers WordPress's `_doing_it_wrong( 'is_home', ... )` notice.
 *
 * With WP_DEBUG_DISPLAY enabled that notice is echoed into the response
 * before headers are sent ("headers already sent" / output started in
 * functions.php). On the block editor that corrupts the editor bootstrap,
 * so Gutenberg cannot load the freshly created accommodation auto-draft and
 * shows: "You attempted to edit an item that doesn't exist. Perhaps it was
 * deleted?"
 *
 * This replaces Bogo's callback with a version that only evaluates is_home()
 * after the `wp` action has fired (i.e. once the main query exists), which is
 * exactly when is_home() is reliable. Behaviour is otherwise unchanged.
 *
 * @package easy-hotel
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action( 'plugins_loaded', 'eshb_fix_bogo_sticky_is_home', 1 );

function eshb_fix_bogo_sticky_is_home() {
	if ( ! function_exists( 'bogo_option_sticky_posts' ) ) {
		return; // Bogo not active – nothing to do.
	}

	remove_filter( 'option_sticky_posts', 'bogo_option_sticky_posts', 10 );

	add_filter( 'option_sticky_posts', 'eshb_bogo_option_sticky_posts', 10, 1 );
}

function eshb_bogo_option_sticky_posts( $posts ) {
	// Only let Bogo run its is_home()-based filtering once the main query is
	// available; before that, is_home() is meaningless and noisy.
	if ( did_action( 'wp' ) ) {
		return bogo_option_sticky_posts( $posts );
	}

	return $posts;
}

/**
 * Stop Bogo's block-editor script from breaking the editor for our CPTs.
 *
 * Bogo's `bogo-block-editor` script registers a GLOBAL apiFetch middleware that
 * appends `lang=<slug>` to every block-editor REST request, with no check for
 * whether the post type is localizable. For a non-localizable CPT such as
 * eshb_accomodation, Gutenberg's post fetch then becomes
 *   /wp/v2/eshb_accomodation/<id>?context=edit&lang=<slug>
 * When that slug is a bare language (e.g. "en", which Bogo emits when the
 * locale is "alone"), the request triggers a _doing_it_wrong notice. With
 * WP_DEBUG_DISPLAY on, that notice HTML is dumped into the REST response, so the
 * JSON is no longer parseable. Gutenberg's getEntityRecord then resolves empty
 * and renders: "You attempted to edit an item that doesn't exist."
 *
 * Bogo's own Language panel renders nothing for non-localizable post types, so
 * the script has no purpose on these screens. Dequeue it there.
 */
add_action( 'admin_enqueue_scripts', 'eshb_dequeue_bogo_block_editor', 999 );

function eshb_dequeue_bogo_block_editor( $hook_suffix ) {
	if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	if ( ! function_exists( 'bogo_is_localizable_post_type' ) ) {
		return; // Bogo not active.
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen || empty( $screen->post_type ) ) {
		return;
	}

	// Leave Bogo alone for post types it actually localizes (post, page, ...).
	if ( bogo_is_localizable_post_type( $screen->post_type ) ) {
		return;
	}

	wp_dequeue_script( 'bogo-block-editor' );
}
