<?php

namespace Deployer;

desc('Execute backup');
task('artisan:backup', artisan('backup:run', ['showOutput', 'runInCurrent']))
    ->onStage('production');
