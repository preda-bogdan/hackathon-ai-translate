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

use mysql_xdevapi\BaseResult;

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

	public function translate( $content_to_translate ) {
		if ( empty( $content_to_translate ) ) {
			return [];
		}
		if ( ! $this->can_use_api() ) {
			return [];
		}

		$prompts = $this->prepare_call( $content_to_translate );
		$response = $this->request( $prompts );
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

	public function request( $prompts ) {
		
		$request_body = [
			'tokens'            => $prompts,
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


	//{"id":"cmpl-6q2PUYoCZ4EQayKg5PGpLEf1pCAFn","object":"text_completion","created":1677860340,"model":"text-davinci-003","choices":[{"text":"\\n\\nBonjour tout le monde","index":0,"logprobs":null,"finish_reason":"stop"}],"usage":{"prompt_tokens":10,"completion_tokens":10,"total_tokens":20}}
	public function get_text_from_response( $response = null ) {
		if ( $response === null ) {
			$response = '{"id":"cmpl-6q2PUYoCZ4EQayKg5PGpLEf1pCAFn","object":"text_completion","created":1677860340,"model":"text-davinci-003","choices":[{"text":"\\n\\nBienvenue sur WordPress. C\'est votre premier article. Éditez-le ou supprimez-le, puis commencez à écrire !","index":0,"logprobs":null,"finish_reason":"stop"}],"usage":{"prompt_tokens":10,"completion_tokens":10,"total_tokens":20}}';
		}
		if ( $response ) {
			$response = json_decode( $response );
			if ( isset( $response->choices ) && ! empty( $response->choices ) ) {
				return trim( $response->choices[0]->text );
			}
		}
		return '';
	}
}