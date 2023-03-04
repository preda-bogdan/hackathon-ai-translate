<?php
/**
 * Description translator.php
 *
 * Author:      Bogdan Preda <bogdan.preda@themeisle.com>
 * Created on:  03-03-{2023}
 *
 * @package hackathon-ai-translate
 */
namespace HackathonAiTranslate;

use function Sodium\add;

class Translator {

	private $supported_locale = [
		'fr' => 'fr_FR',
	];

	private $allowed_translations = [
		'Search',
		'Required fields are marked %s',
		'Post Comment',
	];

	private $translated_strings = [];

	public function __construct() {
		require_once( plugin_dir_path( __FILE__ ) . '/libraries/action-scheduler/action-scheduler.php' );
		add_action('translate_pending', array( $this, 'translate_pending' ) );
		add_action( 'init', array( $this, 'init' ) );
		add_filter( 'locale', array( $this, 'change_locale' ) );
		add_filter( 'gettext', array( $this, 'translate' ), 10, 3 );
		add_action( 'wp_footer', array( $this, 'save_cache' ) );
	}

	public function translate_pending( $transient_name ) {
		$content_to_translate = get_transient( $transient_name );
		if ( $content_to_translate ) {
			$parser = new Parser( $content_to_translate );
			$parser->process_tags();
			// here do the cache update ?
		}
	}
	public function init() {
		as_schedule_single_action( time(), 'translate_pending', array( 'value_to_pass' => 'transient_name' ) );
		$locale = get_locale();
		if ( ! in_array( $locale, array_values( $this->supported_locale ) ) ) {
			return;
		}
		$cache = $this->get_cahed_file( $locale );
		if ( ! is_array( $cache ) ) {
			$cache = json_decode( $cache, true );
		}
		$this->translated_strings = $cache;
	}

	public function save_cache() {
		$locale = get_locale();
		if ( ! in_array( $locale, array_values( $this->supported_locale ) ) ) {
			return;
		}
		$cache = $this->get_cahed_file( $locale );
		if ( ! is_array( $cache ) ) {
			$cache = json_decode( $cache, true );
		}
		$cache = array_merge( $cache, $this->translated_strings );
		$this->write_to_cache( $locale, $cache );
	}

	public function translate( $translated, $original, $domain ) {
		$locale = get_locale();
		if ( ! in_array( $locale, array_values( $this->supported_locale ) ) ) {
			return $translated;
		}
		$cache = $this->get_cahed_file( $locale );
		if ( isset( $cache[ $original ] ) ) {
			return $cache[ $original ];
		}
		if ( $this->is_translatable( $original, $domain ) ) {
			return $this->translated_strings[ $original ];
		}
		return $translated;
	}

	private function is_translatable( $original, $domain ) {
		if ( $domain !== 'default' ) {
			return false;
		}

		if ( ! in_array( $original, $this->allowed_translations, true ) ) {
			return false;
		}

		// add to strings to translate.
		if ( ! isset( $this->translated_strings[ $original ] ) ) {
			$this->translated_strings[$original] = $original;
		}
	}

	public function get_cahed_file( $locale = 'fr' ) {
		$path = AI_TRANSLATE_CACHE . $locale . '.json';
		require_once ABSPATH . '/wp-admin/includes/file.php';
		global $wp_filesystem;
		WP_Filesystem();

		$json  = $wp_filesystem->get_contents( $path );
		$translations = json_decode( $json, true );

		if ( ! is_array( $translations ) ) {
			return [];
		}

		return $translations;
	}

	public function write_to_cache( $locale = 'fr', $translations = [] ) {
		$path = AI_TRANSLATE_CACHE . $locale . '.json';
		require_once ABSPATH . '/wp-admin/includes/file.php';
		global $wp_filesystem;
		WP_Filesystem();
		return $wp_filesystem->put_contents( $path, json_encode( $translations ) );
	}

	public function change_locale( $locale ) {
		if ( is_admin() ) {
			return $locale;
		}
		if ( ! isset( $_GET['lang'] ) ) {
			return $locale;
		}
		if ( in_array( $_GET['lang'], array_keys( $this->supported_locale ) ) ) {
			return $this->supported_locale[ $_GET['lang'] ];
		}
		return $locale;
	}

}