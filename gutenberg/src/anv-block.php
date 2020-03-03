<?php
/**
 * Register our Anvato block
 *
 * @see https://wordpress.org/gutenberg/handbook/blocks/writing-your-first-block-type/#enqueuing-block-scripts
 */

add_action(
	'enqueue_block_editor_assets',
	function () {
		wp_enqueue_script(
			'anv-block',
			plugin_dir_url( __FILE__ ) . '../build/anv-block.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' ),
			filemtime( '../gutenberg/build/anv-block.js' )
		);

		wp_enqueue_style(
			'anv-block-style',
			plugin_dir_url( __FILE__ ) . './anv-block-style.css',
			array(),
			filemtime( './anv-block-style.css' )
		);
	}
);
