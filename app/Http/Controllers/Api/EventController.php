<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller as BaseController;
use App\Http\Resources\EventResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Event;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use JetBrains\PhpStorm\NoReturn;

class EventController extends BaseController implements HasMiddleware
{
    use CanLoadRelationships;
    use AuthorizesRequests;

    private readonly array $relations;

    public function __construct()
    {
        $this->relations = [ 'user', 'attendees', 'attendees.user' ];
    }

    public static function middleware(): array
    {
        return [ new Middleware('auth:sanctum', except: [ 'index', 'show' ]), ];

    }

    /**
     * Display a listing of the resource.
     */
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
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
    public function store(Request $request): \Illuminate\Http\JsonResponse|EventResource
    {
        try {
            $validatedData = $request->validate([ 'name'        => 'required|string|max:255',
                                                  'description' => 'nullable|string',
                                                  'start_time'  => 'required|date',
                                                  'end_time'    => 'required|date|after:start_time', ]);

            $event = Event::create([ ...$validatedData,
                                     'user_id' => $request->user()->id, ]);

            //return $event;
            //return new EventResource($event);
            return new EventResource($this->loadRelationships($event));

        } catch (\Exception $e) {
            // Log the error
            Log::error($e->getMessage());

            // Return the error response
            return response()->json([ 'error' => $e->getMessage() ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event): EventResource
    {
        //return $event;
        //$event->load('user', 'attendees');
        //return new EventResource($event);
        return new EventResource($this->loadRelationships($event));
    }

    /**
     * Update the specified resource in storage.
     * @throws AuthorizationException
     */
    public function update(Request $request, Event $event): EventResource
    {
        /*if(Gate::denies('update-event', $event)) {
            abort(403, 'You are not allowed to update this event.');
        }*/

        Gate::authorize('update-event', $event);
        //$this->authorize('update-event', $event);

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
    public function destroy(Event $event): \Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        $event->delete();

        //return response()->json(['message' => 'Event deleted successfully'], 200);
        return response(status: 204);
    }
}
