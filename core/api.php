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
	public function __construct( $env = [] ) {
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

	public function request( $prompt ) {
		
		$request_body = [
			'prompt'            => $prompt,
			'max_tokens'        => 100,
			'temperature'       => 0.7,
			'top_p'             => 1.0,
			'frequency_penalty' => 0,
			'presence_penalty'  => 0,
			'best_of'           => 1,
			'stream'            => false,
		];
		
		$post_fields = json_encode( $request_body );
		$curl		= curl_init();
		curl_setopt_array(
			$curl,
			array(
				CURLOPT_URL            => 'https://api.openai.com/v1/engines/text-davinci-003/completions',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => '',
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 30,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => 'POST',
				CURLOPT_POSTFIELDS     => $post_fields,
				CURLOPT_HTTPHEADER     => array(
					'Authorization: Bearer ' . $this->secret_key,
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