<?php

namespace App\Observers;

// use App\Classes\ModelCache;
use App\Models\Rubric;

class RubricObserver
{
    /**
     * Handle the Rubric "created" event.
     *
     * @param  \App\Models\Rubric  $rubric
     * @return void
     */
    public function created(Rubric $rubric)
    {
        // ModelCache::updateCache(Rubric::class);
    }

    /**
     * Handle the Rubric "updated" event.
     *
     * @param  \App\Models\Rubric  $rubric
     * @return void
     */
    public function updated(Rubric $rubric)
    {
        // ModelCache::updateCache(Rubric::class);
    }

    /**
     * Handle the Rubric "deleted" event.
     *
     * @param  \App\Models\Rubric  $rubric
     * @return void
     */
    public function deleted(Rubric $rubric)
    {
        // ModelCache::updateCache(Rubric::class);
    }

    /**
     * Handle the Rubric "restored" event.
     *
     * @param  \App\Models\Rubric  $rubric
     * @return void
     */
    public function restored(Rubric $rubric)
    {
        // ModelCache::updateCache(Rubric::class);
    }

    /**
     * Handle the Rubric "force deleted" event.
     *
     * @param  \App\Models\Rubric  $rubric
     * @return void
     */
    public function forceDeleted(Rubric $rubric)
    {
        // ModelCache::updateCache(Rubric::class);
    }
}
