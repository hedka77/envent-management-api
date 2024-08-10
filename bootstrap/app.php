<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

//use Illuminate\Routing\Route;

return Application::configure(basePath: dirname(__DIR__))
                  ->withRouting(web      : __DIR__ . '/../routes/web.php',
                                api      : __DIR__ . '/../routes/api.php',
                                commands : __DIR__ . '/../routes/console.php',
                                health   : '/up',
                                apiPrefix: 'api/v1',
                  )
                  ->withMiddleware(function(Middleware $middleware) {
                      //
                  })
                  ->withExceptions(function(Exceptions $exceptions) {
                      //
                  })
                  ->create();