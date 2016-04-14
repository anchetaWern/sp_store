<?php
namespace App\Lib;

class HttpRequest {

	private $headers;
	private $client;

	public function __construct(){
		$this->headers = [
			'Ocp-Apim-Subscription-Key' => getenv('CS_KEY'),
			'Content-Type' => 'application/json',
			'Accept' => 'application/json'
		];
		$this->client = new \GuzzleHttp\Client(
			['base_uri' => 'https://westus.api.cognitive.microsoft.com']
		);
	}

	public function make($type, $endpoint, $body){
		
		try{
			$response = $this->client->request(
				$type, $endpoint, 
				[
					'headers' => $this->headers, 
					'body' => $body
				]
			);
			$response_body = json_decode($response->getBody()->getContents(), true);
			
			if($response->getStatusCode() == 202){
				$operation_id = $response->getHeaderLine('x-aml-ta-request-id');
				return [
					'operation_id' => $operation_id
				];
			}

			return $response_body;

		}catch(RequestException $e){
			if($e->hasReponse()){
				$error_data = json_decode($e->getResponse()->getBody()->getContents(), true);
				//log error
				return ['error' => $error_data];
			}
		}


	}

}