<?php

namespace Sota\System\Logging;

use Illuminate\Http\Request;

class RequestIdProcessor {

    /** @var Request  */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function __invoke($record)
    {
        if ($this->request->hasHeader('X-Request-Id')) {
            $record['extra']['request-id'] = $this->request->header('X-Request-Id');
        }
        return $record;
    }

}