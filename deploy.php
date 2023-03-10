<?php

namespace Deployer;

require 'recipe/symfony.php';

// Project name
set('application', 'foodtickets');

// Config
set('repository', 'git@github.com:math-98/foodtickets.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', false);

// Shared files/dirs between deploys
add('shared_files', []);
add('shared_dirs', []);

// Writable dirs by web server
set('allow_anonymous_stats', false);

import('deploy-config.yml');

// Tasks
task('npm:install', function () {
    run('cd {{release_path}} && npm install');
});
task('npm:assets', function () {
    run('cd {{release_path}} && npm run build');
});
task('bugsnag:notify', function () {
    $revision = runLocally('git log -n 1 --format="%h"');
    run("cd {{release_path}} && php bin/console bugsnag:deploy --repository {{repository}} --revision {$revision}");
});

// Install NPM dependecies after Composer
after('deploy:vendors', 'npm:install');

// Compile assets after installed NPM
after('npm:install', 'npm:assets');

// Hooks
after('deploy:failed', 'deploy:unlock');
after('deploy', 'bugsnag:notify');

// Migrate database before symlink new release.
before('deploy:symlink', 'database:migrate');
