<?php

namespace App\Providers;

use App\Http\Middleware\OptionalAuthentication;
use App\Models\Attendee;
use App\Models\Event;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('update-event', static function (User $user, Event $event) {
            return $user->id === $event->user_id;
        });

        /*Gate::define('delete-attendee', static function (User $user, Event $event, Attendee $attendee) {
            Log::debug('Gate Inputs:', ['user' => $user, 'event' => $event, 'attendee' => $attendee]);
            return $user->id === $event->user_id || $user->id === $attendee->user_id;
        });*/

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(
                $request->user()?->id ?: $request->ip()
            );
        });

        //Route::aliasMiddleware('auth.optional', OptionalAuthentication::class);
    }
}
