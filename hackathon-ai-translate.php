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


add_filter( 'the_content', function ( $content ) {
	global $post;
	if ( $post->ID === 1 ) {
		// do logic here
		error_log( var_export( $content, true ) );
		$parser = new Parser( $content );
		$parser->process_tags();
		$content = $parser->replace_in_content( $content );
	}
	return $content;
}  );


//add_filter( 'gettext', function ( $translation, $text, $domain ) {
//	error_log( var_export( $text, true ) );
//	error_log( var_export( $domain, true ) );
//	return $translation;
//}, 10, 3 );