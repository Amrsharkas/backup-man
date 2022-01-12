#!/usr/bin/env php
<?php
require __DIR__.'/../vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application();

// ... register commands
$application->add(new \Console\App\Commands\BackupDatabaseCommand());
$application->add(new \Console\App\Commands\RestoreDatabaseCommand());
$application->add(new \Console\App\Commands\BackupSiteCommand());
$application->add(new \Console\App\Commands\RestoreSiteCommand());
$application->add(new \Console\App\Commands\SetupCommand());
$application->add(new \Console\App\Commands\AWSConfigureCommand());
$application->run();

