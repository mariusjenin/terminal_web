<?php

use Terminal\TerminalApp;
use Terminal\Manager\DBManager;

require_once __DIR__ . '/../vendor/autoload.php';

session_start();

date_default_timezone_set('Europe/Paris');

DBManager::setup();

$term_app = TerminalApp::getInstance();

$term_app->addRoutes();

$term_app->run();
