<?php

namespace Terminal\Controller\WebAppController;

use Slim\Psr7\Response;
use Slim\Routing\RouteContext;
use Terminal\Controller\MobileAppController\ItineraryMobileController;
use Terminal\Manager\ConnectionManager;
use Terminal\Manager\DirectionManager;
use Terminal\Manager\RedirectManager;
use Terminal\Model\Arret;
use Terminal\Model\Horaire_arret;
use Terminal\Model\Itineraire;
use Terminal\Model\Participe_a_trajet;
use Terminal\Model\Trajet;
use Terminal\Model\Utilisateur;
use Terminal\TerminalApp;
use Terminal\Utils\Utils;
use Terminal\View\HomeView;
use Terminal\View\ItineraryView;
use Terminal\View\TripView;
use function DI\get;

class TripWebController
{

    /**
     * Handles GET requests on /trips/{id_itinerary:.+}
     * @param $req
     * @param Response $resp
     * @param $args
     * @return \Slim\Psr7\Message|Response
     */
    public static function trips_get($req, Response $resp, $args)
    {
        $iv = new TripView();

        $itineraire = Itineraire::find($args["id_itinerary"]);

        if ($itineraire == null) {
            $rm = RedirectManager::getInstance();
            $url = $rm->getUrlRedirect();
            return $resp->withHeader('Location', TerminalApp::getInstance()->urlFor($url["route"], $url["param"]));
        }

        RedirectManager::getInstance()->refreshCookieUrlRedirect($req, $args);


        $trips = Trajet::where("id_itineraire", "=", $args["id_itinerary"])->get()->toArray();

        $map_trips = function ($t) {
            $t["time_begin"] = Horaire_arret::where("id_trajet", "=", $t["id_trajet"])->orderBy("id_arret", "ASC")->first()->heure_passage;
            $t["time_end"] = Horaire_arret::where("id_trajet", "=", $t["id_trajet"])->orderBy("id_arret", "DESC")->first()->heure_passage;
            return $t;
        };

        $trips = array_map($map_trips, $trips);


        $resp->getBody()->write($iv->renderTrips($itineraire, $trips));
        return $resp;
    }

    /**
     * Handles POST requests on /hours-create-trip
     * @param $req
     * @param Response $resp
     * @param $args
     * @return \Psr\Http\Message\ResponseInterface|\Slim\Psr7\Message|Response
     */
    public static function hours_create_trip_post($req, Response $resp, $args)
    {
        try {
            $params = (array)$req->getParsedBody();

            $time_start = $params['time_start'] ?? null;
            $id_itinerary = $params['id_itinerary'] ?? null;
            if ($time_start == null || $id_itinerary == null)
                throw new \DomainException(json_encode(['error' => LANG_DATA['data_missing']]));

            $arret = Arret::where('id_itineraire', '=', $id_itinerary)
                ->select([
                    'nom_arret',
                    'latitude',
                    'longitude'
                ])
                ->orderBy('num_arret')
                ->get()->toArray();

            $coords = array_map(function ($a, $b) {
                return [$a, $b];
            },
                array_column($arret, 'longitude'),
                array_column($arret, 'latitude')
            );

            $array_itinerary = DirectionManager::getInstance()->itinerary_as_array($coords, true);

            $duration_tot = 0;
            $index = 0;
            $time_array = array_reduce($array_itinerary["response"]["segments"], function ($res, $item) use ($arret, $time_start, &$duration_tot, &$index) {
                $duration_tot = $duration_tot + $item["duration"];
                $item = [];
                if ($index == 0) {
                    $first_item["time"] = date("H:i", strtotime($time_start));
                    $first_item["name"] = $arret[0]["nom_arret"];
                    $index++;
                    $res[] = $first_item;
                }

                $item["time"] = date("H:i", strtotime($time_start . ' + ' . round($duration_tot) . "seconds"));
                $item["name"] = $arret[$index]["nom_arret"];
                $index++;

                $res[] = $item;
                return $res;
            }, []);

            $result = $time_array;


        } catch (\Exception $e) {
            $resp->getBody()->write($e->getMessage());
            return $resp->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
        $resp->getBody()->write(json_encode(Utils::array_map_recursive('strval', $result), JSON_FORCE_OBJECT));
        return $resp->withHeader('Content-Type', 'application/json');
    }

    /**
     * Handles GET requests on /create-trip/{id_itinerary:.+}
     * @param $req
     * @param Response $resp
     * @param $args
     * @return \Slim\Psr7\Message|Response
     */
    public static function create_trip_get($req, Response $resp, $args)
    {
        $iv = new TripView();

        $itineraire = Itineraire::find($args["id_itinerary"]);

        if ($itineraire == null) {
            $rm = RedirectManager::getInstance();
            $url = $rm->getUrlRedirect();
            return $resp->withHeader('Location', TerminalApp::getInstance()->urlFor($url["route"], $url["param"]));
        }

        RedirectManager::getInstance()->refreshCookieUrlRedirect($req, $args);

        $drivers = Utilisateur::where("type", "=", Utilisateur::CONDUCTEUR_TYPE)->get()->all();

        $resp->getBody()->write($iv->renderCreateTrip($itineraire, $drivers));
        return $resp;
    }

    /**
     * Handles GET requests on /edit-trip/{id_trip:.+}
     * @param $req
     * @param Response $resp
     * @param $args
     * @return \Slim\Psr7\Message|Response
     */
    public static function edit_trip_get($req, Response $resp, $args)
    {
        $iv = new TripView();

        $trajet = Trajet::find($args["id_trip"])->toArray();

        if ($trajet == null) {
            $rm = RedirectManager::getInstance();
            $url = $rm->getUrlRedirect();
            return $resp->withHeader('Location', TerminalApp::getInstance()->urlFor($url["route"], $url["param"]));
        }

        RedirectManager::getInstance()->refreshCookieUrlRedirect($req, $args);

        $itineraire = Itineraire::find($trajet["id_itineraire"]);

        $drivers = Utilisateur::where("type", "=", Utilisateur::CONDUCTEUR_TYPE)->get()->all();

        $trajet["hour_trip"] = Horaire_arret::join("arret", "arret.id_arret", "horaire_arret.id_arret")
            ->where("id_trajet", "=", $trajet["id_trajet"])
            ->select(["heure_passage", "nom_arret"])
            ->orderBy("num_arret", "ASC")
            ->get()->toArray();
        $trajet["email"] = Utilisateur::find($trajet["id_utilisateur"])->email;

        $resp->getBody()->write($iv->renderCreateTrip($itineraire, $drivers, $trajet));
        return $resp;
    }

    /**
     * Handles POST requests on /create-trip
     * @param $req
     * @param Response $resp
     * @param $args
     * @return \Psr\Http\Message\ResponseInterface|Response
     */
    public static function create_trip_post($req, Response $resp, $args)
    {
        try {
            $params = (array)$req->getParsedBody();

            $repetition = $params['repetition'] ?? null;

            $nb_places = $params['nb_places'] ?? null;
            $date_start = $params['date_start'] ?? null;
            $driver = $params['driver'] ?? null;
            $id_itinerary = $params['id_itinerary'] ?? null;
            $hour_trip = $params['hour_trip'] ?? [];

            if ($id_itinerary == null || $repetition == null || $nb_places == null || $driver == null || $hour_trip == [])
                throw new \DomainException(json_encode(['error' => LANG_DATA['data_missing']]));


            $user = Utilisateur::where("email", "=", $driver)->first();
            if ($user == null)
                throw new \DomainException(json_encode(['error' => LANG_DATA['data_saved_error']]));

            try {
                $trajet = new Trajet();
                switch ($repetition) {
                    case Trajet::UNIQUE_REPETITION:
                    case Trajet::HEBDOMADAIRE_REPETITION:
                    case Trajet::MENSUEL_REPETITION:
                    case Trajet::ANNUEL_REPETITION:
                        $trajet->date_depart = $date_start;
                        break;
                    default:
                        $trajet->date_depart = null;
                        break;
                }
                $trajet->id_itineraire = $id_itinerary;
                $trajet->repetition = $repetition;
                $trajet->place_max = $nb_places;
                $trajet->id_utilisateur = $user->id_utilisateur;
                $trajet->latitude_conducteur = null;
                $trajet->longitude_conducteur = null;
                $trajet->save();

                foreach ($hour_trip as $k => $v) {

                    $hstop = new Horaire_arret();
                    $hstop->id_trajet = $trajet->id_trajet;
                    $arret = Arret::where("id_itineraire", "=", $id_itinerary)
                        ->where("num_arret", "=", $k)
                        ->first();
                    $hstop->id_arret = $arret->id_arret;
                    $hstop->heure_passage = $v;

                    $hstop->save();
                }
            } catch (\Exception $e) {
                throw new \DomainException(json_encode(['error' => LANG_DATA['data_saved_error']]));
            }

        } catch (\Exception $e) {
            $resp->getBody()->write($e->getMessage());
            return $resp->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
        $resp->getBody()->write(TerminalApp::getInstance()->urlFor("web_trips_get", ['id_itinerary' => $id_itinerary]));
        return $resp;
    }

    /**
     * Handles POST requests on /edit-trip
     * @param $req
     * @param Response $resp
     * @param $args
     * @return \Psr\Http\Message\ResponseInterface|Response
     */
    public static function edit_trip_post($req, Response $resp, $args)
    {
        try {
            $params = (array)$req->getParsedBody();

            $repetition = $params['repetition'] ?? null;

            $nb_places = $params['nb_places'] ?? null;
            $date_start = $params['date_start'] ?? null;
            $driver = $params['driver'] ?? null;
            $id_trip = $params['id_trip'] ?? null;
            $hour_trip = $params['hour_trip'] ?? [];

            $trajet = Trajet::find($id_trip);

            if ($trajet == null || $repetition == null || $nb_places == null || $driver == null || $hour_trip == [])
                throw new \DomainException(json_encode(['error' => LANG_DATA['data_missing']]));

            $user = Utilisateur::where("email", "=", $driver)->first();
            if ($user == null)
                throw new \DomainException(json_encode(['error' => LANG_DATA['data_saved_error']]));

            try {

                switch ($repetition) {
                    case Trajet::UNIQUE_REPETITION:
                    case Trajet::HEBDOMADAIRE_REPETITION:
                    case Trajet::MENSUEL_REPETITION:
                    case Trajet::ANNUEL_REPETITION:
                        $trajet->date_depart = $date_start;
                        break;
                    default:
                        $trajet->date_depart = null;
                        break;
                }
                $trajet->repetition = $repetition;
                $trajet->place_max = $nb_places;
                $trajet->id_utilisateur = $user->id_utilisateur;
                $trajet->latitude_conducteur = null;
                $trajet->longitude_conducteur = null;
                $trajet->save();

                foreach ($hour_trip as $k => $v) {

                    $arret = Arret::where("id_itineraire", "=", $trajet->id_itineraire)
                        ->where("num_arret", "=", $k)
                        ->first();
                    $hstop = Horaire_arret::where("id_trajet", "=", $id_trip)
                        ->where("id_arret", "=", $arret->id_arret)->first();
                    $hstop->heure_passage = $v;

                    $hstop->save();
                }
            } catch (\Exception $e) {
                throw new \DomainException(json_encode(['error' => LANG_DATA['data_saved_error']]));
            }

        } catch (\Exception $e) {
            $resp->getBody()->write($e->getMessage());
            return $resp->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
        $resp->getBody()->write(TerminalApp::getInstance()->urlFor("web_trips_get", ['id_itinerary' => $trajet->id_itineraire]));
        return $resp;
    }


    /**
     * Handles POST requests on /delete-trip
     * @param $req
     * @param Response $resp
     * @param $args
     * @return \Psr\Http\Message\ResponseInterface|Response
     */
    public static function delete_trip_post($req, Response $resp, $args)
    {
        try {
            $params = (array)$req->getParsedBody();

            $id_trip = $params['id_trip'] ?? null;

            $trajet = Trajet::find($id_trip);

            self::delete_trip($trajet);

        } catch (\Exception $e) {
            $resp->getBody()->write($e->getMessage());
            return $resp->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
        $resp->getBody()->write(TerminalApp::getInstance()->urlFor("web_trips_get", ['id_itinerary' => $trajet->id_itineraire]));
        return $resp;
    }

    /**
     * Delete a Trip with all the consequences
     * @param $trajet
     */
    public static function delete_trip($trajet)
    {

        if ($trajet == null)
            throw new \DomainException(json_encode(['error' => LANG_DATA['data_missing']]));

        try {
            $p_a_ts = Participe_a_trajet::where("id_trajet", "=", $trajet->id_trajet)->get()->all();
            foreach ($p_a_ts as $p) {
                $p->delete();
            }

            $h_arrets = Horaire_arret::where("id_trajet", "=", $trajet->id_trajet)->get()->all();
            foreach ($h_arrets as $ha) {
                $ha->delete();
            }

            $trajet->delete();
        } catch (\Exception $e) {
            throw new \DomainException(json_encode(['error' => LANG_DATA['data_saved_error']]));
        }
    }

}

?>
