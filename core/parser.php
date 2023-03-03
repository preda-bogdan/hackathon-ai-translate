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

	public function process_tags() {
		foreach ( $this->tags_list as $tag ) {
			$paragraph = $this->dom->getElementsByTagName( $tag );
			error_log( var_export( $paragraph->item( 0 )->nodeValue, true ) );
		}
	}
}