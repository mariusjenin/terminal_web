<?php

namespace Terminal\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;
use Terminal\Manager\ConnectionManager;
use Terminal\Manager\LanguageManager;
use Terminal\Manager\RedirectManager;
use Terminal\Model\Utilisateur;

/**
 * Middleware qui charge la bonne langue grâce au LanguageManager
 * Class LangMiddleware
 * @package Terminal\Middleware
 */
class LangMiddleware
{
    /**
     * Charge la bonne langue pour le site admin
     *
     * @param ServerRequest $request PSR-7 request
     * @param RequestHandler $handler PSR-15 request handler
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequest $request, RequestHandler $handler): ResponseInterface
    {

        $lm = LanguageManager::getInstance();

        $lm->include_LANG();

        return $handler->handle($request);
    }
}
