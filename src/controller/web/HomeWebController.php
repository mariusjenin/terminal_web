<?php

namespace Terminal\Controller\WebAppController;

use Slim\Psr7\Response;
use Terminal\Manager\RedirectManager;
use Terminal\TerminalApp;
use Terminal\View\HomeView;

class HomeWebController
{

    /**
     * Handles GET requests on /home
     * @param $req
     * @param Response $resp
     * @param $args
     * @return Response
     */
    public static function home_get($req, Response $resp, $args)
    {
        RedirectManager::getInstance()->refreshCookieUrlRedirect($req, $args);
        $hv = new HomeView();
        $resp->getBody()->write($hv->renderHome());
        return $resp;
    }

    public static function previous($req, Response $resp, $args)
    {
        $url = RedirectManager::getInstance()->getUrlRedirect();
        return $resp->withHeader('Location', TerminalApp::getInstance()->urlFor($url["route"], $url["param"]))->withStatus(302);
    }
}

?>
