<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendeeResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class AttendeeController extends Controller implements HasMiddleware
{
    use CanLoadRelationships;
    use AuthorizesRequests;

    private readonly array $relations;

    public function __construct()
    {
        $this->relations = ['user'];
    }

    public static function middleware(): array
    {
        return [new Middleware('auth:sanctum', except: ['index', 'show', 'update']),];
    }

    public function index(Event $event)
    {
        Gate::authorize('viewAny', Attendee::class);

        $attendees = $this->loadRelationships($event->attendees()->latest());

        return AttendeeResource::collection($attendees->paginate(15));
    }


    public function store(Request $request, Event $event)
    {
        $attendee = $this->loadRelationships($event->attendees()->create(['user_id' => 1]));

        return new AttendeeResource($attendee);
    }


    public function show(Event $event, Attendee $attendee)
    {
        return new AttendeeResource($this->loadRelationships($attendee));
    }


    public function destroy(Event $event, Attendee $attendee)
    {
        Log::debug('Authenticated User:', ['user' => auth()->user()]);
        Log::debug('Event Data:', ['event' => $event]);
        Log::debug('Attendee Data:', ['attendee' => $attendee]);

        //Gate::authorize('delete-attendee', [$event, $attendee]);
        $attendee->delete();

        return response(null, 204);
    }
}
