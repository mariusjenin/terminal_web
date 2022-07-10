<?php

namespace Terminal\Controller\WebAppController;

use Slim\Psr7\Response;
use Terminal\Manager\RedirectManager;
use Terminal\Model\Participe_a_trajet;
use Terminal\Model\Trajet;
use Terminal\Model\Utilisateur;
use Terminal\TerminalApp;
use Terminal\Utils\Utils;
use Terminal\View\HomeView;
use Terminal\View\UserView;

class UserWebController
{

    /**
     * Handles GET requests on /create-account
     * @param $req
     * @param Response $resp
     * @param $args
     * @return Response
     */
    public static function create_account_get($req, Response $resp, $args)
    {
        RedirectManager::getInstance()->refreshCookieUrlRedirect($req, $args);
        $uv = new UserView();
        $resp->getBody()->write($uv->renderCreateAccount());
        return $resp;
    }

    /**
     * Handles GET requests on /users
     * @param $req
     * @param Response $resp
     * @param $args
     * @return Response
     */
    public static function users_get($req, Response $resp, $args)
    {
        RedirectManager::getInstance()->refreshCookieUrlRedirect($req, $args);
        $uv = new UserView();

        $users = Utilisateur::get()->all();

        $resp->getBody()->write($uv->renderUsers($users));
        return $resp;
    }


    /**
     * Handles POST requests on /create-account
     * @param $req
     * @param Response $resp
     * @param $args
     * @return \Psr\Http\Message\ResponseInterface|Response
     */
    public static function create_account_post($req, Response $resp, $args)
    {
        $params = (array)$req->getParsedBody();
        $email = $params['email'] ?? '';
        $pwd = $params['password'] ?? '';
        $pwd_confirm = $params['password2'] ?? '';
        $type = $params['type'] ?? '';
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

            if ($type != Utilisateur::CONDUCTEUR_TYPE && $type != Utilisateur::ADMINISTRATEUR_TYPE)
                throw new \DomainException(json_encode(['error' => LANG_IDENTIFICATION['sign_up_error_save']]));

            // save a new user with the given credentials and various information
            $u = new Utilisateur();
            $u->email = $email;
            $u->hash_pwd = $hash_pwd;
            $u->token = Utils::generateToken();
            $u->type = $type;

            if (!$u->save())
                throw new \InvalidArgumentException(json_encode(['error' => LANG_IDENTIFICATION['sign_up_error_save']]));

        } catch (\DomainException $e) {

            $resp->getBody()->write($e->getMessage());
            return $resp->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        } catch (\InvalidArgumentException $e) {
            $resp->getBody()->write($e->getMessage());
            return $resp->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }

        $resp->getBody()->write(TerminalApp::getInstance()->urlFor("web_users_get"));
        return $resp;
    }

    /**
     * Handles POST requests on /delete-user
     * @param $req
     * @param Response $resp
     * @param $args
     * @return \Psr\Http\Message\ResponseInterface|Response
     */
    public static function delete_user_post($req, Response $resp, $args)
    {
        try {
            $params = (array)$req->getParsedBody();

            $id_user = $params['id_user'] ?? null;

            $user = Utilisateur::find($id_user);

            if ($user == null)
                throw new \DomainException(json_encode(['error' => LANG_DATA['data_missing']]));

            try {
                $p_a_ts = Participe_a_trajet::where("id_utilisateur", "=", $user->id_utilisateur)->get()->all();
                foreach ($p_a_ts as $p) {
                    $p->delete();
                }

                $trajets = Trajet::where("id_utilisateur", "=", $user->id_utilisateur)->get()->all();
                foreach ($trajets as $t) {
                    TripWebController::delete_trip($t);
                }

                $user->delete();
            } catch (\Exception $e) {
                throw new \DomainException(json_encode(['error' => LANG_DATA['data_saved_error']]));
            }

        } catch (\Exception $e) {
            $resp->getBody()->write($e->getMessage());
            return $resp->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
        $resp->getBody()->write(json_encode(['success' => LANG_DATA['data_saved_success']]));
        return $resp;
    }

    /**
     * Handles POST requests on /edit-password
     * @param $req
     * @param Response $resp
     * @param $args
     * @return \Psr\Http\Message\ResponseInterface|Response
     */
    public static function edit_password_post($req, Response $resp, $args)
    {
        try {
            $params = (array)$req->getParsedBody();

            $id_user = $params['id_user'] ?? null;
            $password = $params['password'] ?? null;
            $password2 = $params['password2'] ?? null;

            $user = Utilisateur::find($id_user);

            if ($user == null)
                throw new \DomainException(json_encode(['error' => LANG_DATA['data_missing']]));


            $pwd_len = strlen($password);
            if ($pwd_len < 6)
                throw new \DomainException(json_encode(['error' => LANG_IDENTIFICATION['sign_up_pwd_too_short']]));
            if ($password !== $password2)
                throw new \DomainException(json_encode(['error' => LANG_IDENTIFICATION['sign_up_pwd_differents']]));

            $user->hash_pwd = Utils::hash($password);;
            $user->token = Utils::generateToken();
            $user->save();

        } catch (\Exception $e) {
            $resp->getBody()->write($e->getMessage());
            return $resp->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
        $resp->getBody()->write(json_encode(['success' => LANG_DATA['data_saved_success']]));
        return $resp;
    }


}

?>
