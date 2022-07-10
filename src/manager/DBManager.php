<?php

namespace Terminal\Manager;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

include_once __DIR__ . '/../../config/config.php';

/**
 * Manager se chargeant de charger la base de données
 * Class DBManager
 * @package Terminal\Manager
 */
class DBManager
{
    public static function setup()
    {
        $capsule = new Capsule;
        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => DBHOST,
            'port' => DBPORT,
            'database' => DBNAME,
            'username' => DBUSER,
            'password' => DBPWD,
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ]);

        // set the event dispatcher used by Eloquent models
        $capsule->setEventDispatcher(new Dispatcher(new Container));

        // make the capsule a global singleton
        $capsule->setAsGlobal();

        // setup Eloquent
        $capsule->bootEloquent();


//        Observer :
//        Observé::observe(Observeur::class);
    }
}

?>
