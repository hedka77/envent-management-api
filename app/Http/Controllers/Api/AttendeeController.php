<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendeeResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class AttendeeController extends Controller implements HasMiddleware
{
    use CanLoadRelationships;

    private readonly array $relations;

    public function __construct()
    {
        $this->relations = [ 'user' ];
    }

    public static function middleware(): array
    {
        return [ new Middleware('auth:sanctum', except: [ 'index', 'show', 'store', 'destroy' ] ) , ];

    }

    public function index(Event $event)
    {
        $attendees = $this->loadRelationships($event->attendees()->latest());

        return AttendeeResource::collection($attendees->paginate(5));
    }


    public function store(Request $request, Event $event)
    {
        $attendee = $this->loadRelationships($event->attendees()->create([ 'user_id' => 1 ]));

        return new AttendeeResource($attendee);
    }


    public function show(Event $event, Attendee $attendee)
    {
        return new AttendeeResource($this->loadRelationships($attendee));
    }


    public function destroy(Event $event, Attendee $attendee)
    {
        Gate::authorize('delete-attendee', [$event, $attendee]);
        $attendee->delete();

        return response(null, 204);
    }
}
