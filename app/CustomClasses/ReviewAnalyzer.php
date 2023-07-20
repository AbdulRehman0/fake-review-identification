<?php
namespace App\CustomClasses;

use App\Models\Review as ModelsReview;
use App\Models\SuspeciousKeyword;

class ReviewAnalyzer
{
    private $id;
    private $reviewText;
    private $rating;
    private $userId;
    private $isFake;
    private $scores;
    private $reason;

    public function __construct($id, $reviewText, $rating, $userId)
    {
        $this->id = $id;
        $this->reviewText = $reviewText;
        $this->rating = $rating;
        $this->userId = $userId;
        $this->isFake = false;
        $this->scores = [];
        $this->reason = '';
    }

    // Getters and setters for review properties (id, reviewText, rating, userId, isFake, scores, reason)
    // ...

    public function analyze($analyzer, $keywordMatchWeight, $reviewsMatchWeight, $punctuationWeight, $sentimentWeight, $overlyRatedWeight, $scoreThreshold, $punctuationThreshold)
    {
        $sentimentWeightedPercentage = 0;
        $reviewWordCount = str_word_count($this->reviewText);
        $overlyRated = 0;
        $punctuationVal = preg_match_all('/[[:punct:]]/', $this->reviewText) / strlen($this->reviewText);

        // Sentiment Analysis
        $sentiment = $analyzer->getSentiment($this->reviewText);
        if ($sentiment["pos"] >= 0.75 || $sentiment["neg"] >= 0.75) {
            $sentimentWeightedPercentage = $sentimentWeight * 100;
        }

        // Check if review is overly rated
        if ($this->rating > 4 || $this->rating <= 1) {
            $overlyRated = $overlyRatedWeight;
        }

        // User's previous reviews
        $userReviews = $this->getUserReviews(); // Fetch user's previous reviews (database query)

        $similarTones = [];
        foreach ($userReviews as $userReview) {
            similar_text($this->reviewText, $userReview->review, $matchPercentage);
            $similarTones[] = $matchPercentage;
        }

        $matchedKeywords = $this->getMatchedKeywords(); // Fetch matched keywords (database query)
        $keywordMatchPercentage = $matchedKeywords * 100 / $reviewWordCount;
        $reviewsMatchCountPercentage = collect($similarTones)->average();

        // Calculate review score
        if ($keywordMatchPercentage == 0) {
            $score = $reviewsMatchCountPercentage / 100;
        } else {
            $score = (
                ($keywordMatchPercentage * $keywordMatchWeight) +
                ($reviewsMatchCountPercentage * $reviewsMatchWeight) +
                ($overlyRated * 100) +
                ($punctuationVal * $punctuationWeight * 100) +
                ($sentimentWeightedPercentage)
            ) / 100;
        }

        $this->scores = [
            "weightedReviewsMatchCountPercentage" => $reviewsMatchCountPercentage * $reviewsMatchWeight,
            "weightedKeywordMatchPercentage" => $keywordMatchPercentage * $keywordMatchWeight,
            "totalScore" => $score,
            "weightedPunctuationUsage" => $punctuationVal * $punctuationWeight * 100,
            "punctuationUsage" => $punctuationVal * 100,
            "sentimentWeightedPercentage" => $sentimentWeightedPercentage,
        ];

        // Final Review Assessment
        $review = ModelsReview::findOrFail($this->id);
        if ($score > $scoreThreshold) {
            $review->is_fake = true;
            $review->scores = collect($this->scores)->toJson();
            $review->reason = "Score threshold breached";
            $review->save();
            // $this->isFake = true;
            // $this->reason = "Score threshold breached";
        } elseif ($punctuationVal > $punctuationThreshold) {
            $review->is_fake = true;
            $review->scores = collect($this->scores)->toJson();
            $review->reason = "Punctuation threshold breached";
            $review->save();
        }
    }

    private  function getUserReviews(){
        return ModelsReview::where('user_id',$this->userId)->where('id',"!=",$this->id)->get();
    }


    private function getMatchedKeywords(){
        $matchedKeywords=0;
        $keywords = SuspeciousKeyword::all();
        /* The code block is checking if there are any suspicious keywords stored in
        the database. If there are, it iterates over each keyword and checks if the review text
        contains that keyword (case-insensitive comparison). If a keyword is found in the review
        text, the `matchedKeywords` variable is incremented. This is used later in the code to
        calculate the keyword match percentage. */
        if($keywords->count() > 0){
            foreach($keywords as $keyword){
                if(str_contains(strtolower($this->reviewText),strtolower($keyword->keyword))){
                    $matchedKeywords++;
                }
            }
        }
        return $matchedKeywords;

    }
}

