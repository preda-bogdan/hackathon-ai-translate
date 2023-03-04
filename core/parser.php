<?php
/**
 * Description parser.php
 *
 * Author:      Bogdan Preda <bogdan.preda@themeisle.com>
 * Created on:  03-03-{2023}
 *
 * @package hackathon-ai-translate
 */
namespace HackathonAiTranslate;

class Parser {

	const TRANSIENT_NAMESPACE = 'hackathon_ai_translate_';

	private $dom;

	private $tokens_list = [];

	private $tags_list = [ 'p' ];

	private $sections_list = [
		[
			'tag'  => 'ul',
			'class' => 'primary-menu-ul nav-ul',
		],
		[
			'tag'  => 'div',
			'class' => 'entry-header',
		],
		[
			'tag'  => 'div',
			'class' => 'entry-content',
		],
		[
			'tag'  => 'div',
			'class' => 'widget',
		],
	];

	private $buffer = '';

	public function __construct( $content ) {
		$this->dom = new \DomDocument( '1.0', 'UTF-8' );
		try {
			$this->buffer = $content;
			$this->dom->loadHTML( $content, LIBXML_NOERROR | LIBXML_NOWARNING );
		} catch ( \Exception $e ) {
			error_log( var_export( $e->getMessage(), true ) );
		}
	}

	public function replace_in_content( $content ) {
		foreach ( $this->tokens_list as $token ) {
			$content = str_replace( $token['original'], $token['translated'], $content );
		}
		return $content;
	}

	private function get_translated_tokens() {
		$api = new Api();
		foreach ( $this->tokens_list as $id => $token ) {
			$translated = $api->get_text_from_response();
			$this->tokens_list[$id]['translated'] = $translated;
		}
	}

	private function get_inner_html( $node ) {
		//$innerHTML = '';
		$innerHTML = $node->ownerDocument->saveXML( $node );
		error_log( var_export( $innerHTML, true ) );
//		$children  = $node->childNodes;
//		foreach ( $children as $child ) {
//			$innerHTML .= $child->ownerDocument->saveXML( $child );
//		}
		return $innerHTML;
	}

	private function find_by_class( $class, $tag = '' ) {
		if ( ! empty( $tag ) ) {
			$tag = '/' . $tag;
		}
		$xpath = new \DOMXPath( $this->dom );
		$elements = $xpath->query( "//*" . $tag . "[contains(concat(' ', normalize-space(@class), ' '), ' $class ')]" );
		return $elements;
	}

	private function save_for_translation( $locale = 'en_US' ) {
		if ( empty( $this->tokens_list ) ) {
			return;
		}

		// save transient with tokens_list
		$transient_name = self::TRANSIENT_NAMESPACE . $locale . '_' . time();
		set_transient( $transient_name, $this->tokens_list, 60 * 60 * 24 );
		as_schedule_single_action( time(), 'translate_pending', array( 'value_to_pass' => [ $transient_name, $locale ],  ) );
	}

		private function create_html_element_from_string( $string ) {
			$dom = new \DOMDocument( '1.0', 'UTF-8' );
			$dom->loadHTML( mb_convert_encoding( $string, 'HTML-ENTITIES', 'UTF-8' ) , LIBXML_NOERROR | LIBXML_NOWARNING );
			$dom->encoding = 'UTF-8';
			return $dom->documentElement->firstChild;
		}

	public function process_tags() {
		$locale = get_locale();
		$translator = new Translator();
		$translations_cache = $translator->get_cahed_file( $locale );
		foreach ( $this->sections_list as $section ) {
			$elements = $this->find_by_class( $section['class'], $section['tag'] );
			for ( $i = 0; $i < $elements->length; $i++ ) {
				$element = $elements->item( $i );
				$value  = $this->get_inner_html( $element );
				$id     = md5( $value );
				if ( isset( $translations_cache[ $id ] ) ) {
						$parent = $element->parentNode;
						$node = $this->create_html_element_from_string( base64_decode( $translations_cache[ $id ] ) );
						$import_node = $parent->ownerDocument->importNode( $node, true );
						error_log( var_export( $this->get_inner_html( $import_node ), true ) );
						error_log( var_export( $this->get_inner_html( $element ), true ) );
						$parent->replaceChild( $import_node, $element );
						error_log( var_export( $element, true ) );
						$this->buffer = $this->dom->saveHTML();
					continue;
				}
				$this->tokens_list[$id] = base64_encode( $value );
			}
		}
		$this->save_for_translation( $locale );
		return $this->buffer;
	}
}