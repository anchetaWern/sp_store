<?php
namespace App\Lib;

class TextAnalyzer {

	private $HttpRequest;

	public function __construct(){
		$this->HttpRequest = new HttpRequest();
	}

	public function formatDocs($docs){
		$body = [
			'documents' => $docs
		];
		return json_encode($body);
	}
	
	public function requestSentiments($docs){
		$body = $this->formatDocs($docs);
		return $this->HttpRequest->make('POST', '/text/analytics/v2.0/sentiment', $body);
	}

	public function requestKeyPhrases($docs){
		$body = $this->formatDocs($docs);
		return $this->HttpRequest->make('POST', '/text/analytics/v2.0/keyPhrases', $body);
	}

	public function requestTopics(){
		$body = $this->formatDocs($docs);
		return $this->HttpRequest->make('POST', '/text/analytics/v2.0/topics', $body);
	}

	public function getAnalysis($request_id){
		return $this->HttpRequest->make('GET', "/text/analytics/v2.0/operations/{$request_id}");
	}

}