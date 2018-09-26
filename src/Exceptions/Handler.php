<?php

namespace Sota\System\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\App;

class Handler extends ExceptionHandler
{
    public function report(Exception $exception)
    {
        if (!App::environment('local')) {
            if (app()->bound('sentry') && $this->shouldReport($exception)) {
                app('sentry')->captureException($exception);
            }
        }
    
        parent::report($exception);
    }
}
