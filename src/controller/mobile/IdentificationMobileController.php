<?php

namespace Terminal\Controller\MobileAppController;

use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Terminal\Model\Utilisateur;
use Terminal\Utils\Utils;

class IdentificationMobileController
{


    /**
     * Handles POST requests on /mobile/sign-up
     *
     * @note Some fields in this request need to be set, namely:
     *       - string `email` the email of the newly created user account
     *       - string `password` the password for the new account, which must be at least 6 characters long
     *       - string `password_confirm` which must be the exact same as the `password` parameter
     *
     * @param Request $req the PSR-7 Request object which originates from the HTTP request
     *
     * @param Response $resp
     *
     * @param array<mixed> $args
     *
     * @return Response
     */
    public static function sign_up_post(Request $req, Response $resp, $args)
    {
        $params = (array)$req->getParsedBody();
        $email = $params['email'] ?? '';
        $pwd = $params['pwd'] ?? '';
        $pwd_confirm = $params['pwd_confirm'] ?? '';
        try {
            if (empty($email = htmlspecialchars($email)))
                throw new \DomainException(json_encode(['error' => LANG_IDENTIFICATION['email_empty']]));
            if (!filter_var($email, FILTER_VALIDATE_EMAIL))
                throw new \DomainException(json_encode(['error' => LANG_IDENTIFICATION['sign_up_bad_syntax_email']]));

            $pwd_len = strlen($pwd);
            if ($pwd_len < 6)
                throw new \DomainException(json_encode(['error' => LANG_IDENTIFICATION['sign_up_pwd_too_short']]));
            if ($pwd !== $pwd_confirm)
                throw new \DomainException(json_encode(['error' => LANG_IDENTIFICATION['sign_up_pwd_differents']]));

            $hash_pwd = Utils::hash($pwd);

            $u = Utilisateur::where('email', '=', $email)->first();

            if ($u)
                throw new \DomainException(json_encode(['error' => LANG_IDENTIFICATION['sign_up_account_existing']]));

            // save a new user with the given credentials and various information
            $u = new Utilisateur();
            $u->email = $email;
            $u->hash_pwd = $hash_pwd;
            $u->token = Utils::generateToken();
            $u->type = Utilisateur::PASSAGER_TYPE;

            if (!$u->save())
                throw new \InvalidArgumentException(json_encode(['error' => LANG_IDENTIFICATION['sign_up_error_save']]));

        } catch (\DomainException $e) {
            // We use exceptions here to stop processing parameters as long as one does not satisfy a given
            // predicate.
            // The exception `$e` contains a valid JSON string that can be sent back to the application.

            $resp->getBody()->write($e->getMessage());
            return $resp->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        } catch (\InvalidArgumentException $e) {
            $resp->getBody()->write($e->getMessage());
            return $resp->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
        $resp->getBody()->write(json_encode(
            [
                'success' => LANG_IDENTIFICATION['sign_up_success'],
                'result' => [
                    'token' => "$u->token",
                    'email' => $u->email,
                    'type' => $u->type
                ]
            ]
        ));
        return $resp;
    }

    /**
     * Handles POST requests on /mobile/sign-in
     *
     * @note The only two fields required here are:
     *       - string `email` the email of the user trying to log in
     *       - string `password` the password he thinks is his
     *
     * @param Request $req
     *
     * @param Response $resp
     *
     * @param array<mixed> $args
     *
     * @return Response
     */
    public static function sign_in_post(Request $req, Response $resp, $args)
    {
        $params = (array)$req->getParsedBody();

        $email = $params['email'] ?? '';
        $pwd = $params['pwd'] ?? '';

        try {
            if (empty($email = htmlspecialchars($email)))
                throw new \DomainException(json_encode(['error' => LANG_IDENTIFICATION['email_empty']]));

            $u = Utilisateur::where('email', '=', $email)->first();
            if (!$u)
                throw new \DomainException(json_encode(['error' => LANG_IDENTIFICATION['sign_in_email_unknown']]));
            if (!Utils::verify_hash($pwd, $u->hash_pwd))
                throw new \DomainException(json_encode(['error' => LANG_IDENTIFICATION['sign_in_bad_login_password']]));

        } catch (\DomainException $e) {
            // We use exceptions here to stop processing parameters as long as one does not satisfy a given
            // predicate.
            //
            // The exception `$e` contains a valid JSON string that can be sent back to the application.

            $resp->getBody()->write($e->getMessage());
            return $resp->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }

        $resp->getBody()->write(json_encode(
            [
                'success' => LANG_IDENTIFICATION['sign_in_success'],
                'result' => [
                    'id_utilisateur' => "$u->id_utilisateur",
                    'email' => $u->email,
                    'type' => $u->type,
                    'token' => $u->token
                ]
            ]));
        return $resp;
    }

    /**
     * Handles POST requests on /mobile/sign-in-auto
     *
     * @note The only field required here is a string `token` the token of the user
     *
     * @param Request $req
     *
     * @param Response $resp
     *
     * @param array<mixed> $args
     *
     * @return Response
     */
    public static function sign_in_auto_post(Request $req, Response $resp, $args)
    {
        $params = (array)$req->getParsedBody();

        $token = $params['token'] ?? '';

        try {
            if (empty($token))
                throw new \DomainException();

            $u = Utilisateur::where('token', '=', $token)->first();
            if (!$u)
                throw new \DomainException();
            /*
                        $new_token = Utils::generateToken();
                        $u->token = $new_token;

                        if(!$u->save())
                            throw new \DomainException();
            */
        } catch (\DomainException $e) {

            $resp->getBody()->write(json_encode(['error' => LANG_IDENTIFICATION['sign_in_auto_error']]));
            return $resp->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }

        $resp->getBody()->write(json_encode(
            [
                'success' => LANG_IDENTIFICATION['sign_in_auto_success'],
                'result' => [
                    'id_utilisateur' => "$u->id_utilisateur",
                    'email' => $u->email,
                    'type' => $u->type,
                    'token' => $u->token
                ]
            ]));
        return $resp;
    }
}

?>
