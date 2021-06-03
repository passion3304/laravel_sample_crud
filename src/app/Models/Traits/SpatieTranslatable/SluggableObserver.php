<?php

namespace Backpack\CRUD\app\Models\Traits\SpatieTranslatable;

use Illuminate\Contracts\Events\Dispatcher;

class SluggableObserver extends \Cviebrock\EloquentSluggable\SluggableObserver
{
    /**
     * SluggableObserver constructor.
     *
     * @param \Cviebrock\EloquentSluggable\Services\SlugService $slugService
     * @param \Illuminate\Contracts\Events\Dispatcher           $events
     */
    public function __construct(SlugService $slugService, Dispatcher $events)
    {
        parent::__construct($slugService, $events);
    }
}
