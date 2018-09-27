<?php

namespace Deployer;

set(
    'upload_options',
    [
        'options' => [
        '--exclude=.git',
    ],
]);

set('sentry', [
    'organization' => env('SENTRY_ORG'),
    'project' => env('SENTRY_PROJECT'),
    'token' => env('SENTRY_AUTH_TOKEN'),
    'version' => env('RELEASE')
]);