<?php


namespace Terminal\Manager;


use Slim\Psr7\Request;

/**
 * Manager qui se charge de l'url de redirection (retour en arrière dans le site ou redirection avec NotFound)
 *
 * Class RedirectManager
 *
 * @package Vroum\Controler
 */
class RedirectManager
{
    private static $_instance;
    private $home;
    private $welcome;

    private function __construct()
    {
        $this->home = ["route" => "web_home_get", "param" => []];
        $this->welcome = ["route" => "web_login_get", "param" => []];
    }

    public static function getInstance(): RedirectManager
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new RedirectManager();
        }
        return self::$_instance;
    }

    //On stocke toujours la page de redirection si l'utilisateur veut se connecter ou se deconnecter par exemple
    public function refreshCookieUrlRedirect(Request $request, $args)
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        $_SESSION["urlRedirect"] = ["route" => $request->getAttribute("__route__")->getName(), "param" => $args];
    }

    //Getter de l'url de redirection (stocké dans la session)
    public function getUrlRedirect()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        if (isset($_SESSION["urlRedirect"])) {
            $url = $_SESSION["urlRedirect"];

            $_SESSION['urlRedirect'] = "";
            unset($_SESSION['urlRedirect']);

            return $url;
        }

        $cm = ConnectionManager::getInstance();
        if ($cm->getIdConnected() != false) {
            return $this->home;
        } else {
            return $this->welcome;
        }
    }

}
