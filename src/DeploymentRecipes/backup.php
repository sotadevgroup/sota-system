<?php

namespace Deployer;

desc('Execute backup in old release');
task('artisan:backup_old', artisan('backup:run', ['showOutput', 'runInCurrent']))
    ->onStage('production');

desc('Execute backup');
task('artisan:backup', artisan('backup:run', ['showOutput']))
    ->onStage('production');
