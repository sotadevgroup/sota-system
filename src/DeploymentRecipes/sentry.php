<?php
/* (c) Viacheslav Ostrovskiy <chelout@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Deployer;

set('sentry_sota', [
    'token' => env('SENTRY_AUTH_TOKEN'),
    'organization' => env('SENTRY_ORG'),
    'project' => env('SENTRY_PROJECT'),
    'version' => env('CI_COMMIT_TAG'),
]);

desc('Notifying Sentry of Sota deployment');
task('deploy:sentry_sota', function () {
    global $php_errormsg;

    $defaultConfig = [
        'version'       => trim(runLocally('git log -n 1 --format="%h"')),
        'ref'           => null,
        'url'           => null,
        'date_started'   => date("c"),
        'date_released'  => date("c"),
        'sentry_server'  => 'https://sentry.io',
    ];

    $config = array_merge($defaultConfig, (array) get('sentry_sota'));

    if (!is_array($config)) {
        throw new \RuntimeException("Sentry Release Tagging: config array not set");
    }

    if (!isset($config['token'])) {
        throw new \RuntimeException("Sentry Release Tagging: token not set");
    }

    if (!isset($config['organization'])) {
        throw new \RuntimeException("Sentry Release Tagging: organization not set");
    }

    if (!isset($config['project'])) {
        throw new \RuntimeException("Sentry Release Tagging: project not set");
    }

    if (!isset($config['version'])) {
        throw new \RuntimeException("Sentry Release Tagging: version not set");
    }

    $postData = [
        'version'       => $config['version'],
        'ref'           => $config['ref'],
        'url'           => $config['url'],
        'dateStarted'   => $config['date_started'],
        'dateReleased'  => $config['date_released'],
    ];

    $options = array('http' => array(
        'method' => 'POST',
        'header' => "Authorization: Bearer " . $config['token'] . "\r\n" . "Content-type: application/json\r\n",
        'content' => json_encode($postData),
    ));

    $context = stream_context_create($options);
    $result = file_get_contents($config['sentry_server'] . '/api/0/projects/' . $config['organization'] . '/' . $config['project'] . '/releases/', false, $context);

    if (!$result) {
        throw new \RuntimeException($php_errormsg);
    }

})->onStage('production');