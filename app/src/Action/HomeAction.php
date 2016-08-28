<?php
namespace App\Action;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use \App\Lib\Reviews;
use \App\Lib\TextAnalyzer;
use \App\Lib\TextFormatter;
    
final class HomeAction
{

    private $view;
    private $logger;

    public function __construct(Twig $view, LoggerInterface $logger)
    {
        $this->view = $view;
        $this->logger = $logger;
        
        $filter = new \Twig_SimpleFilter('highlight', function ($item) {
           $key_phrases = json_decode($item['key_phrases'], true);

           $highlighted_key_phrases = array_map(function($value){
             return "<span class='highlight'>{$value}</span>";
           }, $key_phrases);

           return str_replace($key_phrases, $highlighted_key_phrases, $item['review']);
            
        });

        $this->view->getEnvironment()->addFilter($filter);
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        $reviews = new Reviews();
        $text_analyzer = new TextAnalyzer();
     
        $avg_sentiment = $reviews->getAverageSentiment();
        $key_phrases = $reviews->getKeyPhrases();
        $topics = $reviews->getTopics();

        $labels = ['Good', 'Bad'];
        $colors = ['#46BFBD', '#F7464A'];
        $highlights = ['#5AD3D1', '#FF5A5E'];

        $first_value = $avg_sentiment;
        $second_value = 1 - $avg_sentiment;

        if ($second_value > $first_value) {
            $labels = array_reverse($labels);
            $colors = array_reverse($colors);
            $highlights = array_reverse($highlights);
        }

        $sentiments_data = [
            [
                'value' => $first_value,
                'label' => $labels[0],
                'color' => $colors[0],
                'highlight' => $highlights[0]
            ],
            [
                'value' => $second_value,
                'label' => $labels[1],
                'color' => $colors[1],
                'highlight' => $colors[1]
            ]
        ];

        $page_data = [
            'app_name' => getenv('APP_NAME'),
            'sentiments_data' => json_encode($sentiments_data),
            'key_phrases' => $key_phrases,
            'topics' => $topics
        ];
        $this->view->render($response, 'home.twig', $page_data);

    }
}
