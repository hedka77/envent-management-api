<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;
use JetBrains\PhpStorm\NoReturn;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //return Event::all();
        //return EventResource::collection(Event::all());
        $query     = Event::query();
        $relations = [ 'user', 'attendees', 'attendees.user' ];

        foreach ($relations as $relation) {
            $query->when($this->shouldIncludeRelation($relation), fn($q) => $q->with($relation));
        }

        //return EventResource::collection(Event::with('user')->paginate(10));
        return EventResource::collection($query->latest()->paginate(10));
    }

    protected function shouldIncludeRelation(string $relation): bool
    {
        $include = request()->query('include');

        if (!$include) {
            return false;
        }

        $relations = array_map('trim', explode(',', $include));

        return in_array($relation, $relations, true);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $event = Event::create([ ...$request->validate([ 'name'        => 'required|string|max:255',
                                                         'description' => 'nullable|string',
                                                         'start_time'  => 'required|date',
                                                         'end_time'    => 'required|date|after:start_time' ]),
                                 'user_id' => 1 ]);

        //return $event;
        return new EventResource($event);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        //return $event;
        $event->load('user', 'attendees');
        return new EventResource($event);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $event->update($request->validate([ 'name'        => 'sometimes|string|max:255',
                                            'description' => 'nullable|string',
                                            'start_time'  => 'sometimes|date',
                                            'end_time'    => 'sometimes|date|after:start_time' ]));

        //return $event;
        return new EventResource($event);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();

        //return response()->json(['message' => 'Event deleted successfully'], 200);
        return response(status: 204);
    }
}