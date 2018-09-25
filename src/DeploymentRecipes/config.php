<?php

namespace Deployer;

desc('Execute artisan config:clear');
task('artisan:config:clear', artisan('config:clear'));
