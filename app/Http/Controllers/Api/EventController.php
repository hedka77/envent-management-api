<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\NoReturn;

class EventController extends Controller
{
    use CanLoadRelationships;

    private readonly array $relations;

    public function __construct()
    {
        $this->relations = [ 'user', 'attendees', 'attendees.user' ];
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //return Event::all();
        //return EventResource::collection(Event::all());
        $query = $this->loadRelationships(Event::query());

        //$query     = Event::query();
        //return EventResource::collection(Event::with('user')->paginate(10));

        return EventResource::collection($query->latest()->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $validatedData = $request->validate([
                                                'name'        => 'required|string|max:255',
                                                'description' => 'nullable|string',
                                                'start_time'  => 'required|date',
                                                'end_time'    => 'required|date|after:start_time',
                                                ]);

            $event = Event::create([
                                   ...$validatedData,
                                   'user_id' => 1
                                   ]);

            //return $event;
            //return new EventResource($event);
            return new EventResource($this->loadRelationships($event));

        }catch (\Exception $e) {
            // Log the error
            Log::error($e->getMessage());

            // Return the error response
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        //return $event;
        //$event->load('user', 'attendees');
        //return new EventResource($event);
        return new EventResource($this->loadRelationships($event));
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
        //return new EventResource($event);
        return new EventResource($this->loadRelationships($event));
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
