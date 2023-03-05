<?php
/**
 * Description api.php
 *
 * Author:      Bogdan Preda <bogdan.preda@themeisle.com>
 * Created on:  03-03-{2023}
 *
 * @package hackathon-ai-translate
 */
namespace HackathonAiTranslate;

class Api {
	private $secret_key;

	private $api_endpoint = null;
	public function __construct( $use_local = false, $env = [] ) {
		if ( true === $use_local ) {
			$env_file = trailingslashit( AI_TRANSLATE_DIR ) . '.env';
			$env = parse_ini_file( $env_file );
		}
		if ( ! empty( $env ) && isset( $env['OPENAPI_SECRET'] ) && ! empty( $env['OPENAPI_SECRET'] ) ) {
			$this->secret_key = $env['OPENAPI_SECRET'];
		}
		if ( ! empty( $env ) && isset( $env['LAMBDA_URL'] ) && ! empty( $env['LAMBDA_URL'] ) ) {
			$this->api_endpoint = $env['LAMBDA_URL'];
		}
	}

	public function can_use_api() {
		return ! empty( $this->secret_key ) || ! empty( $this->api_endpoint );
	}

	public function translate( $content_to_translate, $locale ) {
		if ( empty( $content_to_translate ) ) {
			return [];
		}
		if ( ! $this->can_use_api() ) {
			return [];
		}

		$prompts = $this->prepare_call( $content_to_translate );
		$response = $this->request( $prompts, $locale );
		if ( empty( $response ) ) {
			return [];
		}
		return json_decode( $response, true );
	}

	private function prepare_call( $content_to_translate ) {
		$prompts = [];
		foreach ( $content_to_translate as $hash => $item ) {
			$prompts[] = [
				'id' => $hash,
				'original' => $item,
				'translated' => '',
			];
		}
		return $prompts;
	}

	public function request( $prompts, $locale ) {

		$language = 'french';
		switch($locale) {
			case 'ro_RO':
				$language = 'romanian';
				break;
			case 'es_ES':
				$language = 'spanish';
				break;
			case 'fr_FR':
				$language = 'french';
				break;
			case 'ser_SER':
				$language = 'serbian';
				break;
		}

		$request_body = [
			'tokens'            => $prompts,
			'language'          => $language,
		];
		
		$post_fields = json_encode( $request_body );
		$curl		= curl_init();
		curl_setopt_array(
			$curl,
			array(
				CURLOPT_URL            => $this->api_endpoint,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => '',
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 30,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => 'POST',
				CURLOPT_POSTFIELDS     => $post_fields,
				CURLOPT_HTTPHEADER     => array(
					'Content-Type: application/json',
				),
			)
		);
		
		$response = curl_exec( $curl );
		$err      = curl_error( $curl );
		
		curl_close( $curl );
		
		if ( $err ) {
			error_log( var_export( 'Error #: ' . $err , true ) );
			return false;
		} else {
			return $response;
		}
	}
}