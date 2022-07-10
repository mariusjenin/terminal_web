<?php

namespace Terminal\Controller\WebAppController;

use Slim\Psr7\Request;
use Terminal\Manager\ConnectionManager;
use Terminal\Manager\RedirectManager;
use Terminal\Model\Utilisateur;
use Terminal\TerminalApp;
use Slim\Psr7\Response;
use Terminal\Utils\Utils;
use Terminal\View\IdentificationView;

class IdentificationWebController
{


    /**
     * Handles POST requests on /login
     *
     * @note The only two fields required here are:
     *       - `string email` the email of the user trying to log in
     *       - `string password` the password he thinks is his
     *
     * @param Request $req
     *
     * @param Response $resp
     *
     * @param array<mixed> $args
     *
     * @return Response
     * */
    public static function login_post($req, $resp, $args)
    {
        $params = (array)$req->getParsedBody();

        $mail = $params['email'] ?? '';
        $pass = $params['password'] ?? '';

        try {
            if (empty($mail))
                throw new \DomainException(json_encode(['err' => LANG_IDENTIFICATION["no_email"]]));


            $u = Utilisateur::where([['email', '!=', "aaa"],
                ['type', '=', Utilisateur::ADMINISTRATEUR_TYPE]])->first();
            if (!$u)
                throw new \DomainException(json_encode(['err' => LANG_IDENTIFICATION["bad_id_password"]]));
            if (!Utils::verify_hash($pass, $u->hash_pwd))
                throw new \DomainException(json_encode(['err' => LANG_IDENTIFICATION["bad_id_password"]]));

            ConnectionManager::getInstance()->setIdConnected($u->id_utilisateur);
        } catch (\DomainException $e) {
            // We use exceptions here to stop processing parameters as long as one does not satisfy a given
            // predicate.
            //
            // The exception `$e` contains a valid JSON string that can be sent back to the application.

            $resp->getBody()->write($e->getMessage());
            return $resp->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $url = RedirectManager::getInstance()->getUrlRedirect();
        $resp->getBody()->write(TerminalApp::getInstance()->urlFor($url["route"], $url["param"]));
        return $resp;
    }

    /**
     * Handles POST requests on /logout
     *
     * Simply disconnects the current user, may there be one at the moment.
     *
     * @param Request $req
     *
     * @param Response $resp
     *
     * @param array<mixed> $args
     *
     * @return Response
     * */
    public static function logout_post($req, $resp, $args)
    {
        if (ConnectionManager::getInstance()->disconnect()) {
            // when the connection manager is able to disconnect the current user (this should never fail)
            // then we simply redirect the user onto the welcome page
            $url = RedirectManager::getInstance()->getUrlRedirect();
            $resp->getBody()->write(TerminalApp::getInstance()->urlFor($url["route"], $url["param"]));
            return $resp;
        }

        $resp->getBody()->write(json_encode(['err' => LANG_IDENTIFICATION["error_logout"]]));
        return $resp->withStatus(503);
    }


    /**
     * Handles GET requests on /login
     * @param $req
     * @param Response $resp
     * @param $args
     * @return Response
     */
    public static function login_get($req, Response $resp, $args)
    {
        $iv = new IdentificationView();
        $resp->getBody()->write($iv->renderLogin());
        return $resp;
    }

}

?>
