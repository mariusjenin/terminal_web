<?php
require_once __DIR__ . '/../vendor/autoload.php';


use Terminal\Manager\DBManager;
use Terminal\Model\Trajet;

DBManager::setup();

//DECLARATIONS
/**
 * Will actualize the date of repetitive trips (such as Weekly/Monthly/Annually trips)
 * to always have the right date in the database instead of doing a lot of
 * calculations at each request on the trip table
 */
function actualizeDateForRepetitiveTrips()
{
    $trips = Trajet::all();
    foreach ($trips as $t) {
        $t->refreshDateTrip();
    }
}

//EXECUTIONS
actualizeDateForRepetitiveTrips();
