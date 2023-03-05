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
use HackathonAiTranslate\Api;
use HackathonAiTranslate\Translator;

if ( ! defined( 'WPINC' ) ) {
	die;
}

error_log( var_export( 'Loaded', true ) );

define( 'AI_TRANSLATE_CACHE', plugin_dir_path( __FILE__ ) . 'cache/' );
define( 'AI_TRANSLATE_DIR', plugin_dir_path( __FILE__ ) );

$vendor_file = trailingslashit( plugin_dir_path( __FILE__ ) ) . 'vendor/autoload.php';
if ( is_readable( $vendor_file ) ) {
	require_once $vendor_file;
}
require_once( plugin_dir_path( __FILE__ ) . 'libraries/action-scheduler/action-scheduler.php' );
$env_file = trailingslashit( plugin_dir_path( __FILE__ ) ) . '.env';
$env = parse_ini_file( $env_file );

//$api = new Api( $env );
//error_log( var_export( $api->get_text_from_response(), true ) );
//error_log( var_export( $api->request( 'Translate this from English to French: Hello World' ), true ) );
//die();

$translator = new Translator();
$translator->load_hooks();

function callback($buffer) {
	// modify buffer here, and then return the updated code
	// error_log( var_export( $buffer, true ) );
	$parser = new Parser( $buffer );
	$buffer = $parser->process_tags();
	return $buffer;
}

function buffer_start() { ob_start("callback"); }

function buffer_end() { ob_end_flush(); }

add_action('wp_head', 'buffer_start');
add_action('wp_footer', 'buffer_end');

function ai_translate_add_button() {
	$translator = new Translator();
	$languages = $translator->get_supported_locale();
	global $wp;

	if ( count( $languages ) >= 1 ) {
		echo '<div class="language-switcher">';
		echo '<ul>';
		$current_url =  add_query_arg( $wp->query_vars, home_url( $wp->request ) );
		echo '<li><a href="' . $current_url . '" title="en_US"><img src="https://flagicons.lipis.dev/flags/4x3/gb.svg" /></a></li>';
		foreach ( $languages as $lang => $locale ) {
			$wp->query_vars['lang'] = $lang;
			$current_url =  add_query_arg( $wp->query_vars, home_url( $wp->request ) );
			echo '<li><a href="' . $current_url . '" title="' . $locale . '"><img src="https://flagicons.lipis.dev/flags/4x3/' . $lang . '.svg" /></a></li>';
		}
		echo '</ul>';
		echo '</div>';
	}
}
add_action( 'wp_footer', 'ai_translate_add_button' );


function ai_translate_add_styles() {
	wp_enqueue_style( 'ai-translate-style', plugin_dir_url( __FILE__ ) . 'style.css' );
}
add_action( 'wp_enqueue_scripts', 'ai_translate_add_styles' );


//add_filter( 'the_content', function ( $content ) {
//	global $post;
//	if ( $post->ID === 1 ) {
//		// do logic here
//		error_log( var_export( $content, true ) );
//		$parser = new Parser( $content );
//		$parser->process_tags();
//		$content = $parser->replace_in_content( $content );
//	}
//	return $content;
//}  );


//add_filter( 'gettext', function ( $translation, $text, $domain ) {
//	error_log( var_export( $text, true ) );
//	error_log( var_export( $domain, true ) );
//	return $translation;
//}, 10, 3 );