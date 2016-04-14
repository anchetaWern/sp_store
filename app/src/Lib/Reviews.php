<?php
namespace App\Lib;

class Reviews {

	private $db;

	public function __construct(){
		$db_host = getenv('DB_HOST');
		$db_name = getenv('DB_NAME');
		$dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8";
		$pdo = new \Slim\PDO\Database($dsn, getenv('DB_USER'), getenv('DB_PASS'));
		
		$this->db = $pdo;
	}

	public function getReviews(){
		$select_statement = $this->db->select(['id', 'review AS text'])
		           ->from('reviews')
		           ->where('analyzed', '=', 0)
		           ->limit(100);

		$stmt = $select_statement->execute();
		$data = $stmt->fetchAll();
		return $data;
	}

	public function getSentiments(){
		//gets sentiments from DB
		$select_statement = $this->db->select()
			->from('review_sentiments');

		$stmt = $select_statement->execute();
		$data = $stmt->fetchAll();
		return $data;		
	}

	public function getTopics(){
		$select_statement = $this->db->select(['topic', 'score'])
			->from('topics')
      ->orderBy('score', 'DESC')
      ->limit(10);
			
		$stmt = $select_statement->execute();
		$data = $stmt->fetchAll();
		return $data;		
	}

	public function getKeyPhrases(){
		$select_statement = $this->db->select(['review', 'key_phrases'])
			->from('review_key_phrases')
      ->join('reviews', 'review_key_phrases.review_id', '=', 'reviews.id')
      ->where('analyzed', '=', 1)
      ->limit(10);
			
		$stmt = $select_statement->execute();
		$data = $stmt->fetchAll();
		return $data;	
	}

	public function saveSentiments($sentiments){	
		foreach($sentiments as $row){
			$review_id = $row['id'];
			$score = $row['score'];
			$insert_statement = $this->db->insert(['review_id', 'score'])
				->into('review_sentiments')
				->values([$review_id, $score]);
			$insert_statement->execute();
		}
	}

	public function saveRequest($request_id, $request_type){
		$insert_statement = $this->db->insert(['request_id', 'request_type', 'done'])
				->into('requests')
				->values([$request_id, $request_type, 0]);
		$insert_statement->execute();
	}

	public function updateRequest($request_id){
		$update_statement = $this->db->update(['done' => 1])
				->table('requests')
				->where('request_id', '=', $request_id);
		$update_statement->execute();
	}

	public function saveTopics($topics_data){
		$topics = $topics_data['topics'];
		foreach($topics as $row){
			$topic_id = $row['id'];
			$topic = $row['keyPhrase'];
			$score = $row['score'];
			$insert_statement = $this->db->insert(['topic_id', 'topic', 'score'])
				->into('topics')
				->values([$topic_id, $topic, $score]);
			$insert_statement->execute();
		}

		$review_topics = $topics_data['review_topics'];
		foreach($review_topics as $row){
			$review_id = $row['documentId'];
			$topic_id = $row['topicId'];
			$distance = $row['distance'];
 			$insert_statement = $this->db->insert(['review_id', 'topic_id', 'distance'])
 				->into('review_topics')
				->values([$review_id, $topic_id, $distance]);
			$insert_statement->execute();
		}
	}

	public function saveKeyPhrases($key_phrases){
		foreach($key_phrases as $row){
			$review_id = $row['id'];
			$phrases = json_encode($row['keyPhrases']);
			$insert_statement = $this->db->insert(['review_id', 'key_phrases'])
				->into('review_key_phrases')
				->values([$review_id, $phrases]);
			$insert_statement->execute();
		}
	}

	public function getPendingRequests(){
		$select_statement = $this->db->select()
			->from('requests')
			->where('done', '=', 0);
			
		$stmt = $select_statement->execute();
		$data = $stmt->fetchAll();
		return $data;	
	}

	public function setDone($from_id, $to_id){
		$update_statement = $this->db->update(['analyzed' => 1])
				->table('reviews')
				->whereBetween('id', [$from_id, $to_id]);
		$update_statement->execute();
	}

	public function getAverageSentiment(){
		$select_statement = $this->db->select()
		           ->from('review_sentiments')
		           ->avg('score', 'avg_sentiment');
		$stmt = $select_statement->execute();
		$data = $stmt->fetch();
		return $data['avg_sentiment'];
	}
}