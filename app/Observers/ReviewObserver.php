<?php

namespace App\Observers;

use App\Jobs\FakeReviewCheck;
use App\Jobs\ReviewIdentificationJob;
use App\Models\Review;

class ReviewObserver
{
    /**
     * Handle the Review "created" event.
     */
    public function created(Review $review): void
    {
        // dispatch(new FakeReviewCheck($review->id));
        dispatch(new ReviewIdentificationJob($review->id));
    }

    /**
     * Handle the Review "updated" event.
     */
    public function updated(Review $review): void
    {

    }

    /**
     * Handle the Review "deleted" event.
     */
    public function deleted(Review $review): void
    {
        //
    }

    /**
     * Handle the Review "restored" event.
     */
    public function restored(Review $review): void
    {
        //
    }

    /**
     * Handle the Review "force deleted" event.
     */
    public function forceDeleted(Review $review): void
    {
        //
    }
}
