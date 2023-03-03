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

	private $dom;

	private $tokens_list = [];

	private $tags_list = [ 'p' ];

	public function __construct( $content ) {
		$this->dom = new \DomDocument();
		try {
			$this->dom->loadHTML( $content );
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

	public function process_tags() {
		foreach ( $this->tags_list as $tag ) {
			$paragraph = $this->dom->getElementsByTagName( $tag );
			$value  = $paragraph->item( 0 )->nodeValue;
			$id     = md5( $value );
			$this->tokens_list[$id]['original'] = $value;
			$this->tokens_list[$id]['translated'] = $value;
			error_log( var_export( $value, true ) );
		}
		$this->get_translated_tokens();
	}
}