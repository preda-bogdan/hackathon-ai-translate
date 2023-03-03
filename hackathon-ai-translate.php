<?php
/**
 * Plugin Name:       AI Translate
 * Description:       This plugin is a hackathon project to test the AI translation capabilities.
 * Version:           2.5.3
 * Plugin URI:        https://ai-translate.test
 * Author:            DreamTeam
 * License:           MIT
 * License URI:       https://opensource.org/license/mit
 * Text Domain:       hack-ai-translate
 * Domain Path:       /languages
 * Requires PHP:      7.4
 *
 * @package HackathonAITranslate
 */
use HackathonAiTranslate\Parser;

if ( ! defined( 'WPINC' ) ) {
	die;
}

error_log( var_export( 'Loaded', true ) );

$vendor_file = trailingslashit( plugin_dir_path( __FILE__ ) ) . 'vendor/autoload.php';
if ( is_readable( $vendor_file ) ) {
	require_once $vendor_file;
}


add_filter( 'the_content', function ( $content ) {
	global $post;
	if ( $post->ID === 1 ) {
		// do logic here
		error_log( var_export( $content, true ) );
		$parser = new Parser( $content );
		$parser->process_tags();
	}
	return $content;
}  );