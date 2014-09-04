<?php

// Uncomment this line if you must temporarily take down your site for maintenance.
// require '.maintenance.php';

$configurator = require __DIR__ . '/../app/bootstrap.php';
$container = $configurator->createContainer();

// Run application.
$container->getService('application')->run();
