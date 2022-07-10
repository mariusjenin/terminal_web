<?php

namespace Terminal\Controller\WebAppController;

use Slim\Psr7\Response;
use Terminal\Controller\MobileAppController\ItineraryMobileController;
use Terminal\Manager\ConnectionManager;
use Terminal\Manager\DirectionManager;
use Terminal\Manager\RedirectManager;
use Terminal\Model\Arret;
use Terminal\Model\Horaire_arret;
use Terminal\Model\Itineraire;
use Terminal\Model\Trajet;
use Terminal\Model\Utilisateur;
use Terminal\TerminalApp;
use Terminal\Utils\Utils;
use Terminal\View\HomeView;
use Terminal\View\ItineraryView;

class ItineraryWebController
{

    /***
     * Handles GET requests on /itineraries
     * @param $req
     * @param Response $resp
     * @param $args
     * @return Response
     */
    public static function itineraries_get($req, Response $resp, $args)
    {
        RedirectManager::getInstance()->refreshCookieUrlRedirect($req, $args);
        $iv = new ItineraryView();
        $itineraries = Itineraire::select(['id_itineraire'])->get()->toArray();

        $stringify_id = function ($it) {
            $id = $it['id_itineraire'];
            $it = ItineraryMobileController::get_itinerary_details_array($id);
            return $it;
        };
        $itineraries = array_map($stringify_id, $itineraries);

        $resp->getBody()->write($iv->renderItineraries($itineraries));
        return $resp;
    }


    /**
     * Handles POST requests on /itinerary-json
     * @param $req
     * @param Response $resp
     * @param $args
     * @return \Psr\Http\Message\ResponseInterface|\Slim\Psr7\Message|Response
     */
    public static function itinerary_json_post($req, Response $resp, $args)
    {

        try {
            $params = (array)$req->getParsedBody();

            $user = Utilisateur::find(ConnectionManager::getInstance()->getIdConnected());

            if ($user->type != Utilisateur::ADMINISTRATEUR_TYPE)
                throw new \DomainException(json_encode(['error' => LANG_DATA['cant_access_to_data']]));

            $coords = $params['coords'] ?? null;
            if ($coords == null)
                throw new \DomainException(json_encode(['error' => LANG_DATA['data_missing']]));

            $array_itinerary = DirectionManager::getInstance()->itinerary_as_array($coords, false);
            $result = $array_itinerary["response"]["coordinates"];


        } catch (\Exception $e) {
            $resp->getBody()->write($e->getMessage());
            return $resp->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
        $resp->getBody()->write(json_encode(Utils::array_map_recursive('strval', $result), JSON_FORCE_OBJECT));
        return $resp->withHeader('Content-Type', 'application/json');
    }

    /**
     * Handles GET requests on /create-itinerary
     * @param $req
     * @param Response $resp
     * @param $args
     * @return Response
     */
    public static function create_itinerary_get($req, Response $resp, $args)
    {
        RedirectManager::getInstance()->refreshCookieUrlRedirect($req, $args);
        $iv = new ItineraryView();
        $resp->getBody()->write($iv->renderCreateItinerary());
        return $resp;
    }

    /**
     * Handles POST requests on /create-itinerary
     * @param $req
     * @param Response $resp
     * @param $args
     * @return \Psr\Http\Message\ResponseInterface|Response
     */
    public static function create_itinerary_post($req, Response $resp, $args)
    {

        try {
            $params = (array)$req->getParsedBody();

            $user = Utilisateur::find(ConnectionManager::getInstance()->getIdConnected());

            if ($user->type != Utilisateur::ADMINISTRATEUR_TYPE)
                throw new \DomainException(json_encode(['error' => LANG_DATA['cant_access_to_data']]));

            $stops = $params['stops'] ?? null;
            $name_itinerary = $params['name_itinerary'] ?? "";

            if ($stops == null || $name_itinerary == "")
                throw new \DomainException(json_encode(['error' => LANG_DATA['data_missing']]));

            try {
                $it = new Itineraire();
                $it->nom_itineraire = $name_itinerary;
                $it->save();
                foreach ($stops as $k => $v) {

                    $v = explode(",", $v);
                    $lat = $v[0];
                    $lng = $v[1];
                    $name_stop = $v[2];

                    $stop = new Arret();
                    $stop->id_itineraire = $it->id_itineraire;
                    $stop->nom_arret = $name_stop;
                    $stop->num_arret = $k;
                    $stop->latitude = $lat;
                    $stop->longitude = $lng;

                    $stop->save();
                }
            } catch (\Exception $e) {
                throw new \DomainException(json_encode(['error' => LANG_DATA['data_saved_error']]));
            }

        } catch (\Exception $e) {
            $resp->getBody()->write($e->getMessage());
            return $resp->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
        $resp->getBody()->write(TerminalApp::getInstance()->urlFor("web_itineraries_get"));
        return $resp;
    }

    /**
     * Handles POST requests on /delete-itinerary
     * @param $req
     * @param Response $resp
     * @param $args
     * @return \Psr\Http\Message\ResponseInterface|Response
     */
    public static function delete_itinerary_post($req, Response $resp, $args)
    {

        try {
            $params = (array)$req->getParsedBody();

            $id_itinerary = $params['id_itinerary'] ?? null;

            $itineraire = Itineraire::find($id_itinerary);

            if ($itineraire == null)
                throw new \DomainException(json_encode(['error' => LANG_DATA['data_missing']]));

            try {
                $trajets = Trajet::where("id_itineraire", "=", $itineraire->id_itineraire)->get()->all();
                foreach ($trajets as $t) {
                    TripWebController::delete_trip($t);
                }

                $arrets = Arret::where("id_itineraire", "=", $itineraire->id_itineraire)->get()->all();
                foreach ($arrets as $a) {
                    $a->delete();
                }

                $itineraire->delete();
            } catch (\Exception $e) {
                throw new \DomainException(json_encode(['error' => LANG_DATA['data_saved_error']]));
            }

        } catch (\Exception $e) {
            $resp->getBody()->write($e->getMessage());
            return $resp->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
        $resp->getBody()->write(TerminalApp::getInstance()->urlFor("web_itineraries_get"));
        return $resp;
    }

}

?>
