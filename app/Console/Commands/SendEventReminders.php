<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Notifications\EventReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SendEventReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-event-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends a reminder email to all event attendees when event starts soon';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $events = Event::with('attendees.user')->whereBetween('start_time', [now(), now()->addDays(15)])->limit(5)->get();

        $eventCount = $events->count();
        $eventLabel = Str::plural('event', $eventCount);

        $this->info("Found {$eventCount} {$eventLabel}");

        $i = 0;
        $events->each(fn($event)
            => $event->attendees->each(

                //fn($attendee)
                // => $this->info("Notifying the user {$attendee->user->name}, attendee_id = {$attendee->id} to attend event
                // {$event->name}")

            fn($attendee)
                => $attendee->user->notify(new EventReminderNotification($event))
            )
        );

        $this->info('Reminder notifications sent successfully.');
    }
}
