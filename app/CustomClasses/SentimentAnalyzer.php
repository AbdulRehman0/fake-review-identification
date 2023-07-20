<?php
namespace App\CustomClasses;
Use Sentiment\Analyzer;
class SentimentAnalyzer
{
    public function getSentiment($reviewText)
    {
        $analyzer = new Analyzer();
        return $analyzer->getSentiment($reviewText);
    }
}
