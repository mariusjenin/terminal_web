<?php


namespace Terminal\Manager;

/**
 * Manager qui se charge de la connexion d'un utilisateur
 *
 * Class ConnectionManager
 *
 * @package Vroum\Controler
 */
class ConnectionManager
{

    private static $_instance;

    private function __construct()
    {

    }

    public static function getInstance(): ConnectionManager
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new ConnectionManager();
        }
        return self::$_instance;
    }


    //On stocke l'email de l'utilisateur connecté dans la variable de session
    public function setIdConnected($id)
    {
        $_SESSION["id"] = $id;
    }

    public function getIdConnected()
    {
        if (isset($_SESSION["id"])) {
            return $_SESSION["id"];
        }
        return false;
    }

    public function disconnect()
    {
        $_SESSION['id'] = "";
        unset($_SESSION['id']);

        return !isset($_SESSION['id']);
    }
}
