<?php

/**
 * My Application bootstrap file.
 */
use Nette\Application\Routers\Route,
    Nette\Diagnostics\Debugger;

// Load composer libs
require LIBS_DIR . '/autoload.php';

// Boot Nette Framework 
require LIBS_DIR . '/nette/nette/Nette/loader.php';


// Configure application
$configurator = new Nette\Config\Configurator;

// Enable Nette Debugger for error visualisation & logging
//$configurator->setProductionMode($configurator::AUTO);
$configurator->enableDebugger(__DIR__ . '/../log');
Debugger::$strictMode = true;

// Enable RobotLoader - this will load all classes automatically
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()
	->addDirectory(APP_DIR)
	->addDirectory(LIBS_DIR)
	->register();

// Create Dependency Injection container from config.neon file
$configurator->addConfig(__DIR__ . '/config/config.neon');
$container = $configurator->createContainer();

// Setup router
$container->router[] = new Route('index.php', 'Main:default', Route::ONE_WAY);
$container->router[] = new Route('<presenter>/<action>[/<id>]', 'Main:default');


// Configure and run the application!
$container->application->run();
