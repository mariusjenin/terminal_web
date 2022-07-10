<?php

namespace Terminal\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;
use Terminal\Manager\ConnectionManager;
use Terminal\Manager\RedirectManager;
use Terminal\Model\Utilisateur;

/**
 * Middleware de redirection si l'utilisateur est connecté et est sur une page où il devrait ne pas l'être
 * Class RedirectIfAdminConnectedMiddleware
 * @package Terminal\Middleware
 */
class RedirectIfAdminConnectedMiddleware
{
    /**
     * Redirect si l'utilisateur est connecté
     *
     * @param ServerRequest $request PSR-7 request
     * @param RequestHandler $handler PSR-15 request handler
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequest $request, RequestHandler $handler): ResponseInterface
    {
        $response = new Response();
        $cm = ConnectionManager::getInstance();
        $id_connected = $cm->getIdConnected();
        $user = Utilisateur::find($id_connected);
        if ($user != null && $user->type == Utilisateur::ADMINISTRATEUR_TYPE) {
            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $rm = RedirectManager::getInstance();
            $url = $rm->getUrlRedirect();
            return $response->withHeader('Location', $routeParser->urlFor($url["route"], $url["param"]));
        } else {
            $response = $handler->handle($request);
            return $response;
        }
    }
}
