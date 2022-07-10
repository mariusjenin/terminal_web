<?php

namespace Terminal\Controller\MobileAppController;

use Exception;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Terminal\Manager\DirectionManager;
use Terminal\Model\Arret;
use Terminal\Model\Horaire_arret;
use Terminal\Model\Itineraire;
use Terminal\Model\Participe_a_trajet;
use Terminal\Model\Trajet;
use Terminal\Model\Utilisateur;
use Terminal\Utils\Utils;

class TripMobileController
{

    public static $TRIP_REQUEST = "trip";
    public static $RESERV_REQUEST = "reserv";


    /**
     * Handles GET requests on /mobile/future_trips/{id:.+}
     *
     * @note Give JSON corresponding to all future_trips of an user
     *
     * @param Request $req the PSR-7 Request object which originates from the HTTP request
     *
     * @param Response $resp
     *
     * @param array<mixed> $args
     *
     * @return Response
     */
    public static function future_trips_get(Request $req, Response $resp, $args)
    {
        try {
            $token = $req->getHeaderLine('token');

            if (strlen($token) == 0)
                throw new \DomainException(json_encode(['error' => LANG_DATA['token_missing']]));

            $user = Utilisateur::where("token", "=", $token)->first();

            if ($user == null)
                throw new \DomainException(json_encode(['error' => LANG_DATA['bad_token']]));

            if ($user->type == Utilisateur::CONDUCTEUR_TYPE) {
                $future_trips = Trajet::where('trajet.id_utilisateur', '=', $user->id_utilisateur)
                    ->join('itineraire', 'itineraire.id_itineraire', '=', 'trajet.id_itineraire')
                    ->select(
                        [
                            "trajet.id_trajet",
                            "itineraire.nom_itineraire as titre",
                            "itineraire.id_itineraire",
                            "date_depart"
                        ])
                    ->get()->toArray();
                $future_trips = array_reduce($future_trips, function ($res, $item) {
                    $trip = Trajet::find($item["id_trajet"]);
                    if ($trip->is_valid_for_month(strtotime("now"))) {
                        $start_stop = Arret::where("id_itineraire", "=", $item["id_itineraire"])->orderBy("num_arret", 'ASC')->first();
                        $end_stop = Arret::where("id_itineraire", "=", $item["id_itineraire"])->orderBy("num_arret", 'DESC')->first();
                        $item["arret_depart"] = $start_stop->nom_arret;
                        $item["arret_fin"] = $end_stop->nom_arret;
                        $item["date_depart"] = date(LANG_DATE["format_date"], strtotime($trip->nextDayValid()));
                        $item["heure_depart"] = date(LANG_DATE["format_hour"], strtotime(Horaire_arret::where("id_arret", "=", $start_stop->id_arret)->where("id_trajet", "=", $item["id_trajet"])->first()->heure_passage));
                        $item["heure_fin"] = date(LANG_DATE["format_hour"], strtotime(Horaire_arret::where("id_arret", "=", $end_stop->id_arret)->where("id_trajet", "=", $item["id_trajet"])->first()->heure_passage));
                        unset($item['id_itineraire']);
                        $res[] = $item;
                    }
                    return $res;
                }, []);
            } else {
                $future_trips = Participe_a_trajet::where("participe_a_trajet.id_utilisateur", "=", $user->id_utilisateur)
                    ->join('trajet', 'participe_a_trajet.id_trajet', '=', 'trajet.id_trajet')
                    ->join('itineraire', 'itineraire.id_itineraire', '=', 'trajet.id_itineraire')
                    ->select(
                        [
                            "id_reservation",
                            "participe_a_trajet.id_trajet",
                            "itineraire.nom_itineraire as titre",
                            "itineraire.id_itineraire",
                            "participe_a_trajet.date_participation as date_depart",
                            "participe_a_trajet.id_arret_depart",
                            "participe_a_trajet.id_arret_fin",
                            "participe_a_trajet.latitude_utilisateur as latitude",
                            "participe_a_trajet.longitude_utilisateur as longitude"

                        ])
                    ->get()->toArray();

                //Permet de récupérer les horaires à l'arrêt de départ et à l'arrêt d'arrivée des future_trips
                $manage_future_trips = function ($res, $item) {
                    $date_part = $item['date_depart'];

                    $arret = Horaire_arret::where('id_trajet', '=', $item['id_trajet'])
                        ->join('arret', 'horaire_arret.id_arret', '=', 'arret.id_arret')
                        ->orderBy('num_arret', 'asc')->get()->all();
                    $arret_fin = $arret[count($arret) - 1];

                    $h_fin_traj = $arret_fin->heure_passage;


                    $time_now = strtotime("now");
                    $time_end_trip = strtotime($date_part) + strtotime($h_fin_traj) - strtotime('00:00:00');

                    if ($time_now <= $time_end_trip && Trajet::find($item['id_trajet'])->is_valid_for_month($time_now)) {
                        $arret_dep = array_reduce($arret, function ($carry, $i) use ($item) {
                            return $i->id_arret == $item['id_arret_depart'] ? $i : $carry;
                        });
                        $arret_fin = array_reduce($arret, function ($carry, $i) use ($item) {
                            return $i->id_arret == $item['id_arret_fin'] ? $i : $carry;
                        });

                        if ($item['latitude'] != null && $item['longitude'] != null) {
                            $item['arret_depart'] = LANG_TRIP['future_trips_actual_position'];
                            $distTot = Utils::haversineGreatCircleDistance($arret_dep->latitude, $arret_dep->longitude, $arret_fin->latitude, $arret_fin->longitude);
                            $dist = Utils::haversineGreatCircleDistance($arret_dep->latitude, $arret_dep->longitude, $item['latitude'], $item['longitude']);
                            $time = strtotime($arret_fin->heure_passage) - strtotime($arret_dep->heure_passage);
                            $item['heure_depart'] = date(LANG_DATE["format_hour"], strtotime($arret_dep->heure_passage) + ($time * $dist / $distTot));
                        } else {
                            $item['arret_depart'] = $arret_dep->nom_arret;
                            $item['heure_depart'] = date(LANG_DATE["format_hour"], strtotime($arret_dep->heure_passage));
                        }

                        $item['arret_fin'] = $arret_fin->nom_arret;
                        $item['heure_fin'] = date(LANG_DATE["format_hour"], strtotime($arret_fin->heure_passage));

                        unset($item['latitude']);
                        unset($item['longitude']);
                        unset($item['id_itineraire']);
                        unset($item['id_arret_depart']);
                        unset($item['id_arret_fin']);
                        $res[] = $item;
                    }
                    return $res;
                };
                $future_trips = array_reduce($future_trips, $manage_future_trips, []);
            }

            $result = [
                "success" => LANG_DATA['data_generated_success'],
                "result" => $future_trips
            ];
        } catch (\DomainException $e) {
            // We use exceptions here to stop processing parameters as long as one does not satisfy a given
            // predicate.
            // The exception `$e` contains a valid JSON string that can be sent back to the application.

            $resp->getBody()->write($e->getMessage());
            return $resp->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }

        $resp->getBody()->write(json_encode(Utils::array_map_recursive('strval', $result), JSON_FORCE_OBJECT));
        return $resp->withHeader('Content-Type', 'application/json');
    }

    public static function trip_reserved_get(Request $req, Response $resp, $args)
    {
        try {
            $token = $req->getHeaderLine('token');

            if (strlen($token) == 0)
                throw new \DomainException(json_encode(['error' => LANG_DATA['token_missing']]));

            $user = Utilisateur::where("token", "=", $token)->where("type", "=", Utilisateur::PASSAGER_TYPE)->first();

            if ($user == null)
                throw new \DomainException(json_encode(['error' => LANG_DATA['bad_token']]));

            $id_reserv = $args['id_reservation'] ?? 0;

            $reserv = Participe_a_trajet::find($id_reserv);
            if ($reserv == null)
                throw new \DomainException(json_encode(['error' => LANG_RESERVATION['bad_reservation']]));

            $trip = Trajet::find($reserv->id_trajet);
            if ($trip == null)
                throw new \DomainException(json_encode(['error' => LANG_TRIP['bad_trip']]));


            $coords = Arret::where('id_itineraire', '=', $trip->id_itineraire)
                ->select([
                    'latitude',
                    'longitude'
                ])
                ->orderBy('num_arret')
                ->get()->toArray();

            $coords = array_map(function ($a, $b) {
                return [$a, $b];
            },
                array_column($coords, 'longitude'),
                array_column($coords, 'latitude')
            );

            $itinerary_json = DirectionManager::getInstance()->itinerary_as_array($coords, true);

            $result = self::trip_details($itinerary_json, $trip, $reserv);

        } catch (\DomainException $e) {
            // We use exceptions here to stop processing parameters as long as one does not satisfy a given
            // predicate.
            // The exception `$e` contains a valid JSON string that can be sent back to the application.

            $resp->getBody()->write($e->getMessage());
            return $resp->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }

        $resp->getBody()->write(json_encode(Utils::array_map_recursive('strval', $result), JSON_FORCE_OBJECT));
        return $resp->withHeader('Content-Type', 'application/json');
    }


    public static function trip_driven_get(Request $req, Response $resp, $args)
    {
        try {
            $token = $req->getHeaderLine('token');

            if (strlen($token) == 0)
                throw new \DomainException(json_encode(['error' => LANG_DATA['token_missing']]));

            $user = Utilisateur::where("token", "=", $token)->where("type", "=", Utilisateur::CONDUCTEUR_TYPE)->first();

            if ($user == null)
                throw new \DomainException(json_encode(['error' => LANG_DATA['bad_token']]));

            $id_trip = $args['id_trip'] ?? 0;

            $trip = Trajet::find($id_trip);
            if ($trip == null)
                throw new \DomainException(json_encode(['error' => LANG_TRIP['bad_trip']]));

            $coords = Arret::where('id_itineraire', '=', $trip->id_itineraire)
                ->select([
                    'latitude',
                    'longitude'
                ])
                ->orderBy('num_arret')
                ->get()->toArray();

            $coords = array_map(function ($a, $b) {
                return [$a, $b];
            },
                array_column($coords, 'longitude'),
                array_column($coords, 'latitude')
            );

            $itinerary_json = DirectionManager::getInstance()->itinerary_as_array($coords, true);

            $result = self::trip_details($itinerary_json, $trip);


            if (isset($result["success"])) {
                $pats = Participe_a_trajet::where('id_trajet', "=", $id_trip)
                    ->where('date_participation', "=", date("Y-m-d", strtotime("now")))->get()->toArray();
                $pats = array_filter($pats, function ($item) {
                    return $item["latitude_utilisateur"] != null && $item["longitude_utilisateur"] != null;
                });

                if ($trip->latitude_conducteur != null && $trip->latitude_conducteur != null) {
                    $lat = $trip->latitude_conducteur;
                    $long = $trip->longitude_conducteur;
                } else {
                    $s = Arret::where("id_itineraire", "=", $trip->id_itineraire)
                        ->orderBy('num_arret', "ASC")->first();
                    $lat = $s->latitude;
                    $long = $s->longitude;
                }

                $num_arret = Trajet::getStopBefore($itinerary_json, $lat, $long, 100, $id_in_geo);
                $stops = Arret::where("id_itineraire", "=", $trip->id_itineraire)
                    ->join("horaire_arret", "horaire_arret.id_arret", "arret.id_arret")
                    ->where("id_trajet", "=", $trip->id_trajet)
                    ->where(function ($q) use ($num_arret) {
                        $q->where("num_arret", "=", $num_arret)->orWhere("num_arret", "=", $num_arret + 1);
                    })
                    ->orderBy('num_arret', "ASC")->distinct("nom_arret")->get()->all();
                $latBegin = $stops[0]->latitude;
                $longBegin = $stops[0]->longitude;
                $latEnd = $stops[1]->latitude;
                $longEnd = $stops[1]->longitude;
                $distTot = Utils::haversineGreatCircleDistance($latBegin, $longBegin, $latEnd, $longEnd);
                $time = strtotime($stops[1]->heure_passage) - strtotime($stops[0]->heure_passage);


                $dist = Utils::haversineGreatCircleDistance($lat, $long, $latBegin, $longBegin);
                $time_driver = strtotime($stops[0]->heure_passage) + ($time * $dist / $distTot);

                $pats = array_reduce($pats, function ($res, $item) use ($time_driver, $itinerary_json, $coords, $trip) {
                    if ($item["latitude_utilisateur"] != null && $item["longitude_utilisateur"] != null) {

                        $num_arret = Trajet::getStopBefore($itinerary_json, $item["latitude_utilisateur"], $item["longitude_utilisateur"], 100, $id_in_geo);
                        $stops = Arret::where("id_itineraire", "=", $trip->id_itineraire)
                            ->join("horaire_arret", "horaire_arret.id_arret", "arret.id_arret")
                            ->where("id_trajet", "=", $trip->id_trajet)
                            ->where(function ($q) use ($num_arret) {
                                $q->where("num_arret", "=", $num_arret)->orWhere("num_arret", "=", $num_arret + 1);
                            })
                            ->orderBy('num_arret', "ASC")->get()->all();

                        $latBegin = $stops[0]->latitude;
                        $longBegin = $stops[0]->longitude;
                        $latEnd = $stops[1]->latitude;
                        $longEnd = $stops[1]->longitude;
                        $distTot = Utils::haversineGreatCircleDistance($latBegin, $longBegin, $latEnd, $longEnd);
                        $time = strtotime($stops[1]->heure_passage) - strtotime($stops[0]->heure_passage);

                        $dist = Utils::haversineGreatCircleDistance($latBegin, $longBegin, $item["latitude_utilisateur"], $item["longitude_utilisateur"]);
                        $time_pass = strtotime($stops[0]->heure_passage) + ($time * $dist / $distTot);


                        $duree = round(($time_pass - $time_driver) / 60);
                        if ($duree >= 0) {
                            $res[] = [
                                "id_in_geo" => $id_in_geo,
                                "start_stop_name" => $stops[0]->nom_arret,
                                "start_stop_pos" => $stops[0]->num_arret,
                                "end_stop_name" => $stops[1]->nom_arret,
                                "end_stop_pos" => $stops[1]->num_arret,
                                "time" => $duree
                            ];
                        }
                    }
                    return $res;
                }, []);

                $result["result"]["passenger_coords"] = $pats;
            }

        } catch (\DomainException $e) {
            // We use exceptions here to stop processing parameters as long as one does not satisfy a given
            // predicate.
            // The exception `$e` contains a valid JSON string that can be sent back to the application.

            $resp->getBody()->write($e->getMessage());
            return $resp->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }

        $resp->getBody()->write(json_encode(Utils::array_map_recursive('strval', $result), JSON_FORCE_OBJECT));
        return $resp->withHeader('Content-Type', 'application/json');
    }

    private static function trip_details($itinerary_json, $trip, $reserv = null)
    {

        $arret_itineraire = Arret::where('id_itineraire', '=', $trip->id_itineraire)
            ->join("horaire_arret", "horaire_arret.id_arret", "arret.id_arret")
            ->where("horaire_arret.id_trajet", "=", $trip->id_trajet)
            ->select([
                'horaire_arret.id_arret as id_stop',
                'nom_arret as name_stop',
                'num_arret as num_stop',
                'heure_passage as time',
                'latitude',
                'longitude'
            ])
            ->orderBy('num_arret')
            ->get()->toArray();

        $details_trip = [];
        if ($reserv != null) {
            if ($reserv->latitude_utilisateur != null && $reserv->longitude_utilisateur != null) {
                $details_trip["start_stop"] = Trajet::getStopBefore($itinerary_json, $reserv->latitude_utilisateur, $reserv->longitude_utilisateur, 100, $id_in_geo);
                $details_trip["id_in_geo"] = $id_in_geo;
            } else {
                $details_trip["start_stop"] = Arret::find($reserv->id_arret_depart)->num_arret;
            }
            $details_trip["end_stop"] = Arret::find($reserv->id_arret_fin)->num_arret;
            $details_trip["date"] = date(LANG_DATE["format_date"], strtotime($reserv->date_participation));
        } else {
            $details_trip["start_stop"] = 0;
            $details_trip["end_stop"] = count($arret_itineraire) - 1;
            $details_trip["date"] = date(LANG_DATE["format_date"], strtotime($trip->nextDayValid()));
        }


        if ($reserv != null && $reserv->latitude_utilisateur != null && $reserv->longitude_utilisateur != null) {
            $latBegin = $arret_itineraire[$details_trip["start_stop"]]['latitude'];
            $longBegin = $arret_itineraire[$details_trip["start_stop"]]['longitude'];
            $latEnd = $arret_itineraire[$details_trip["end_stop"]]['latitude'];
            $longEnd = $arret_itineraire[$details_trip["end_stop"]]['longitude'];
            $distTot = Utils::haversineGreatCircleDistance($latBegin, $longBegin, $latEnd, $longEnd);
            $time = strtotime($arret_itineraire[$details_trip["end_stop"]]['time']) - strtotime($arret_itineraire[$details_trip["start_stop"]]['time']);

            $dist = Utils::haversineGreatCircleDistance($latBegin, $longBegin, $reserv->latitude_utilisateur, $reserv->longitude_utilisateur);
            $arret_itineraire[$details_trip["start_stop"]]['time'] = date("H:i", strtotime($arret_itineraire[$details_trip["start_stop"]]['time']) + ($time * $dist / $distTot));
        }

        $details_trip['time_trip'] = (strtotime($arret_itineraire[$details_trip["end_stop"]]["time"]) - strtotime($arret_itineraire[$details_trip["start_stop"]]["time"])) / 60;


        $arret_itineraire = array_map(function ($item) {
            $item["time"] = date(LANG_DATE["format_hour"], strtotime($item["time"]));
            return $item;
        }, $arret_itineraire);

        return [
            "success" => LANG_DATA['data_generated_success'],
            "result" => [
                "itinerary_name" => Itineraire::find($trip->id_itineraire)->nom_itineraire,
                "itinerary_coords" => $itinerary_json,
                "itinerary_stops" => $arret_itineraire,
                "trip" => $details_trip
            ]
        ];
    }


    /**
     * Handles POST requests on /mobile/trip_driven_ongoing
     *
     * @param Request $req the PSR-7 Request object which originates from the HTTP request
     *
     * @param Response $resp
     *
     * @param array<mixed> $args
     *
     * @return Response
     */
    public static function trip_driven_ongoing_post(Request $req, Response $resp, $args)
    {
        try {
            $token = $req->getHeaderLine('token');

            if (strlen($token) == 0)
                throw new \DomainException(json_encode(['error' => LANG_DATA['token_missing']]));

            $user = Utilisateur::where("token", "=", $token)->first();

            if ($user == null || $user->type != Utilisateur::CONDUCTEUR_TYPE)
                throw new \DomainException(json_encode(['error' => LANG_DATA['bad_token']]));

            $params = (array)$req->getParsedBody();


            if (!isset($params["latitude"]) || !isset($params["longitude"]))
                throw new \DomainException(json_encode(['error' => LANG_DATA['data_missing']]));

            $latitude = $params["latitude"] ?? "-1";
            $longitude = $params["longitude"] ?? "-1";

            $id_trip = $params["id_trip"] ?? "-1";

            $trip = Trajet::find($id_trip);

            if ($trip == null && $trip->id_utilisateur == $user->id_utilisateur)
                throw new \DomainException(json_encode(['error' => LANG_DATA['incorrect_id']]));

            $last_stop = Arret::where("id_itineraire", "=", $trip->id_itineraire)
                ->join("horaire_arret", "horaire_arret.id_arret", "arret.id_arret")
                ->orderBy("num_arret", "DESC")->first();
            $first_stop = Arret::where("id_itineraire", "=", $trip->id_itineraire)
                ->join("horaire_arret", "horaire_arret.id_arret", "arret.id_arret")
                ->orderBy("num_arret", "ASC")->first();


            if (strtotime($first_stop->heure_passage . " -30min") > strtotime(date("H:i", strtotime("now"))) ||
                strtotime($last_stop->heure_passage . " +30min") < strtotime(date("H:i", strtotime("now")))) {
                $continue = 0;
                $trip->latitude_conducteur = null;
                $trip->longitude_conducteur = null;
            } else {
                $continue = 1;
                $trip->latitude_conducteur = $latitude;
                $trip->longitude_conducteur = $longitude;
            }

            if ($trip->save()) {
                $result = [
                    'success' => LANG_DATA['data_saved_success'],
                    'result' => [
                        "in_process" => $continue
                    ]
                ];
            } else {
                throw new \DomainException(json_encode(['error' => LANG_DATA["data_saved_error"]]));
            }
        } catch (\DomainException $e) {
            // We use exceptions here to stop processing parameters as long as one does not satisfy a given
            // predicate.
            // The exception `$e` contains a valid JSON string that can be sent back to the application.

            $resp->getBody()->write($e->getMessage());
            return $resp->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }

        $resp->getBody()->write(json_encode(Utils::array_map_recursive('strval', $result), JSON_FORCE_OBJECT));
        return $resp->withHeader('Content-Type', 'application/json');
    }

    /**
     * Handles POST requests on /mobile/trip_reserved_ongoing/{id_reservation:.+}
     *
     * @param Request $req the PSR-7 Request object which originates from the HTTP request
     *
     * @param Response $resp
     *
     * @param array<mixed> $args
     *
     * @return Response
     */
    public static function trip_reserved_ongoing_get(Request $req, Response $resp, $args)
    {
        try {
            $token = $req->getHeaderLine('token');

            if (strlen($token) == 0)
                throw new \DomainException(json_encode(['error' => LANG_DATA['token_missing']]));

            $user = Utilisateur::where("token", "=", $token)->where("type", "=", Utilisateur::PASSAGER_TYPE)->first();

            if ($user == null)
                throw new \DomainException(json_encode(['error' => LANG_DATA['bad_token']]));

            $id_reserv = $args['id_reservation'] ?? 0;

            $reserv = Participe_a_trajet::find($id_reserv);
            if ($reserv == null)
                throw new \DomainException(json_encode(['error' => LANG_RESERVATION['bad_reservation']]));

            $trip = Trajet::find($reserv->id_trajet);
            if ($trip == null)
                throw new \DomainException(json_encode(['error' => LANG_TRIP['bad_trip']]));


            $lat = $trip->latitude_conducteur;
            $long = $trip->longitude_conducteur;
            $isInProcess = $lat != null && $long != null;
            $resInProcess = ($isInProcess) ? "1" : "0";


            $result = [
                'success' => LANG_DATA['data_generated_success'],
                'result' => [
                    "in_process" => $resInProcess
                ]
            ];
            if ($isInProcess) {
                $result["result"]["coords"] = [
                    "latitude" => $lat,
                    "longitude" => $long
                ];
            }

        } catch (\DomainException $e) {
            // We use exceptions here to stop processing parameters as long as one does not satisfy a given
            // predicate.
            // The exception `$e` contains a valid JSON string that can be sent back to the application.

            $resp->getBody()->write($e->getMessage());
            return $resp->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }

        $resp->getBody()->write(json_encode(Utils::array_map_recursive('strval', $result), JSON_FORCE_OBJECT));
        return $resp->withHeader('Content-Type', 'application/json');
    }


}

?>
