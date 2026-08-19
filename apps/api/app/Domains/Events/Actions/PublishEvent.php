<?php


namespace App\Domains\Events\Actions;

use App\Models\Event;

final class publishEvent
{
    public function handle(Event $event): Event
    {
        $event->status = 'published';
        $event->save();

        return $event->refresh();
    }
}