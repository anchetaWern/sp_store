<?php
require 'vendor/autoload.php';

use \App\Lib\TextAnalyzer;
use \App\Lib\Reviews;

class AnalyzeCommand extends ConsoleKit\Command 
{
	
    public function execute(array $args, array $options = array())
    {
        $dotenv = new \Dotenv\Dotenv(__DIR__ . '/../../..');
        $dotenv->load();
    	
        $reviews = new Reviews();
        $text_analyzer = new TextAnalyzer();
           	
		//check if there are pending requests
		$pending_requests = $reviews->getPendingRequests();
		foreach($pending_requests as $request){
	       	    	
			$request_id = $request['request_id'];
			$from_id = $request['from_review'];
			$to_id = $request['to_review'];

			$response = $text_analyzer->getAnalysis($request_id);
			if(strtolower($response['status']) == 'succeeded'){
				$result = $response['operationProcessingResult'];
				$topics = $result['topics'];
				$review_topics = $result['topicAssignments'];
				
				$reviews->saveTopics([
					'topics' => $topics,
					'review_topics' => $review_topics
				]);
					
				$reviews->setDone($from_id, $to_id);
				$reviews->updateRequest($request_id);
			}
		}

		$docs = $reviews->getReviews();
		$total_docs = count($docs);

		if($total_docs == 100){ 
			$from_review = $docs[0]['id'];
			$to_review = $docs[$total_docs - 1]['id'];

            $sentiments_response = $text_analyzer->requestSentiments($docs);	
            $reviews->saveSentiments($sentiments_response['documents']);
            $this->writeln('saved sentiments!');

            $key_phrases_response = $text_analyzer->requestKeyPhrases($docs);
            $reviews->saveKeyPhrases($key_phrases_response['documents']);	
            $this->writeln('saved key phrases!');

			$topics_request_id = $text_analyzer->requestTopics($docs);
			$reviews->saveRequest($topics_request_id, 'topics', $from_review, $to_review);	
			$this->writeln('topics requested! request ID: ' . $topics_request_id);
		}

        $this->writeln('Done!', ConsoleKit\Colors::GREEN);
  }
}

$console = new ConsoleKit\Console();
$console->addCommand('AnalyzeCommand');
$console->run();