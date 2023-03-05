<?php
/**
 * Plugin Name:       AI Translate
 * Description:       This plugin is a hackathon project to test the AI translation capabilities.
 * Version:           1.0.0
 * Plugin URI:        https://ai-translate.test
 * Author:            DreamTeam (Mihai Grigore, Bogdan Preda)
 * License:           MIT
 * License URI:       https://opensource.org/license/mit
 * Text Domain:       hack-ai-translate
 * Domain Path:       /languages
 * Requires PHP:      7.4
 *
 * @package HackathonAITranslate
 */
use HackathonAiTranslate\Parser;
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

$translator = new Translator();
$translator->load_hooks();

function callback($buffer) {
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


function add_query_arg_to_home_url($url, $path, $orig_scheme, $blog_id) {
	$translator = new Translator();
	$languages = $translator->get_supported_locale();
	if ( isset( $_GET['lang'] ) && array_key_exists( $_GET['lang'], $languages ) ) {
		$arg_name = 'lang';
		$arg_value = $_GET['lang'];
		return add_query_arg($arg_name, $arg_value, $url);
	}
	return $url;
}
add_filter('home_url', 'add_query_arg_to_home_url', 10, 4);