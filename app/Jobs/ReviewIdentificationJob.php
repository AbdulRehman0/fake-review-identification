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

class ReviewIdentificationJob implements ShouldQueue
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
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $review = Review::findOrFail($this->review_id);
            $reviewAnalyzer = new ReviewAnalyzer($review->id,$review->review,$review->rating,$review->user_id);
            $this->identifyFakeReview($reviewAnalyzer);

        } catch (Exception $e) {
            Log::error($e);
        }
    }

    private function identifyFakeReview(ReviewAnalyzer $review)
    {
        $sentimentAnalyzer = new SentimentAnalyzer();

        // Configured weights and thresholds (read from config/global.php)
        $keywordMatchWeight = config("global.keyword_match_weight");
        $reviewsMatchWeight = config("global.reviews_match_weight");
        $punctuationWeight = config("global.punctuation_weight");
        $sentimentWeight = config("global.sentiment_weight");
        $overlyRatedWeight = config("global.overly_rated_weight");
        $scoreThreshold = config("global.score");
        $punctuationThreshold = config("global.punctuation_threshold");

        $review->analyze(
            $sentimentAnalyzer,
            $keywordMatchWeight,
            $reviewsMatchWeight,
            $punctuationWeight,
            $sentimentWeight,
            $overlyRatedWeight,
            $scoreThreshold,
            $punctuationThreshold
        );


    }
}
