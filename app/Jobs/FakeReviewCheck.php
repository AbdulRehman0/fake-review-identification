<?php

namespace App\Jobs;

use App\CustomClasses\ReviewAnalyzer;
use App\CustomClasses\SentimentAnalyzer;
use App\Models\Review;
use App\Models\SuspeciousKeyword;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
Use Sentiment\Analyzer;

class FakeReviewCheck implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public $review_id)
    {
        //
    }

    /**
     * The function calculates a score for a review based on various factors such as keyword match
     * percentage, similarity with previous reviews, punctuation value, and rating, and determines if
     * the review is fake or not based on the score and configured thresholds.
     *
     * @return void The function does not return any value. It has a void return type, which means it
     * does not return anything.
     */
    public function handle(): void
    {
        try {

            $analyzer = new Analyzer();
            $keywordMatchPercentageWeight = config("global.keyword_match_weight");
            $reviewsMatchCountPercentageWeight = config("global.reviews_match_weight");
            $puntuationWeight = config("global.punctuation_weight");
            $sentimentWeight = config("global.sentiment_weight");
            $overlyRated = 0;
            $punctuationVal = 0;
            $sentimentWeightedPercentage = 0;

            $review = Review::findOrFail($this->review_id);

            $sentiment = $analyzer->getSentiment($review->review);
            if($sentiment["pos"] >= 0.75 || $sentiment["neg"] >= 0.75){
                $sentimentWeightedPercentage = $sentimentWeight * 100;
            }

            $reviewWordCount = str_word_count($review->review);

            if($review->rating > 4 || $review->rating <= 1 ){
                $overlyRated = config("global.overly_rated_weight");
            }
            $punctuationVal = preg_match_all('/[[:punct:]]/', $review->review) / strlen($review->review);

            /**User previous reviews */
            $userReviews = Review::where('user_id',$review->user_id)->where('id',"!=",$this->review_id)->get();
            $totalReviews = $userReviews->count();
            // check if user has any review on any product
            // if($totalReviews == 0){
            //     return;
            // }

            $similarTones = [];

            foreach($userReviews as $userReview){
                /* The `similar_text()` function is a built-in PHP function that calculates the
                similarity between two strings. In this code, it is used to compare the review text
                of the current review with the review text of each previous review by the same user. */
                similar_text($review->review,$userReview->review,$matchPercectage);
                $similarTones[] =$matchPercectage;
            }
            $matchedKeywords = 0;
            $keywords = SuspeciousKeyword::all();
            /* The code block is checking if there are any suspicious keywords stored in
            the database. If there are, it iterates over each keyword and checks if the review text
            contains that keyword (case-insensitive comparison). If a keyword is found in the review
            text, the `matchedKeywords` variable is incremented. This is used later in the code to
            calculate the keyword match percentage. */
            if($keywords->count() > 0){
                foreach($keywords as $keyword){
                    if(str_contains(strtolower($review->review),strtolower($keyword->keyword))){
                        $matchedKeywords++;
                    }
                }
            }

            $keywordMatchPercentage = $matchedKeywords*100/$reviewWordCount;
            $reviewsMatchCountPercentage = collect($similarTones)->average();

            /* This code block is calculating the score for the review to determine if it is a fake
            review or not. */
            if($keywordMatchPercentage == 0){
                $score = $reviewsMatchCountPercentage / 100;
            }else{
                $score = (
                    ($keywordMatchPercentage*$keywordMatchPercentageWeight) +
                    ($reviewsMatchCountPercentage*$reviewsMatchCountPercentageWeight) +
                    ($overlyRated*100) +
                    ($punctuationVal * $puntuationWeight *100)+
                    ($sentimentWeightedPercentage)
                    )/100;
            }


            $results = [
                "weightedReviewsMatchCountPercentage"=>$reviewsMatchCountPercentage*$reviewsMatchCountPercentageWeight,
                "weightedKeywordMatchPercentage"=>$keywordMatchPercentage*$keywordMatchPercentageWeight,
                "totalScore"=>$score,
                "weightedPunctuationUsage"=>$punctuationVal * $puntuationWeight *100,
                "punctuationUsage"=>$punctuationVal *100,
                "sentimentWeightedPercentage" => $sentimentWeightedPercentage,
            ];

            /* The code block `if( > config("global.score") ||  >
            config("global.punctuation_threshold"))` is checking if the calculated score for the
            review is greater than the configured threshold score or if the punctuation value of the
            review is greater than the configured punctuation threshold. */
            if($score > config("global.score")){
                $review->is_fake = true;
                $review->scores = collect($results)->toJson();
                $review->reason = "Score threshold breached";
                $review->save();
            }elseif($punctuationVal > config("global.punctuation_threshold")){
                $review->is_fake = true;
                $review->scores = collect($results)->toJson();
                $review->reason = "Punctuation threshold breached";
                $review->save();
            }

            Log::info("fris scores",$results);
        } catch (Exception $e) {
            Log::error($e);
        }

    }
}
