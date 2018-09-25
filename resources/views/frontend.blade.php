<?php

    // frontend

    $env = [
        "APP_ENV" => env('APP_ENV'),
        "BUILD" => env('BUILD', null),
        "RELEASE" => env('RELEASE', null),
        "SENTRY_DSN" => env('SENTRY_DSN', null)
    ];

    $html = file_get_contents(public_path() . '/frontend/index.html');

    $html = str_replace('{{env}}', json_encode($env), $html);

    echo $html;