<?php

namespace Terminal\Controller\MobileAppController;

use Exception;
use Illuminate\Support\Arr;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Terminal\Manager\DirectionManager;
use Terminal\Model\Arret;
use Terminal\Model\Horaire_arret;
use Terminal\Model\Participe_a_trajet;
use Terminal\Model\Trajet;
use Terminal\Model\Utilisateur;
use Terminal\Utils\Utils;

class ReservationMobileController
{


    /**
     * Handles POST requests on /mobile/reservation-ongoing
     *
     * @param Request $req the PSR-7 Request object which originates from the HTTP request
     *
     * @param Response $resp
     *
     * @param array<mixed> $args
     *
     * @return Response
     */
    public static function reservation_ongoing_post(Request $req, Response $resp, $args)
    {
        try {
            $params = (array)$req->getParsedBody();

            //Get Id Itineraire
            $id_itineraire = $params['id_itinerary'] ?? 0;
            if ($id_itineraire <= 0)
                throw new \DomainException(json_encode(['error' => LANG_DATA['incorrect_id']]));

            //Get date_depart
            $date_depart = $params['date'] ?? '';
            if (strlen($date_depart) == 0)
                throw new \DomainException(json_encode(['error' => LANG_RESERVATION['reservation_error']]));

            //get Nb Place
            $nb_place = $params['nb_place'] ?? '';
            if (strlen($nb_place) == 0)
                throw new \DomainException(json_encode(['error' => LANG_RESERVATION['reservation_error']]));

            //Get Travel Alone
            if (!isset($params['travel_alone']))
                throw new \DomainException(json_encode(['error' => LANG_RESERVATION['reservation_error']]));
            $travel_alone = (boolean)$params['travel_alone'];

            if (!isset($params['give_coords']))
                throw new \DomainException(json_encode(['error' => LANG_RESERVATION['reservation_error']]));
            $give_coords = (boolean)$params['give_coords'];

            $coords = Arret::where('id_itineraire', '=', $id_itineraire)
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

            $request_with_coords = false;
            if (isset($params['latitude']) && isset($params['longitude'])) {
                $request_with_coords = true;
            }

            $start_stop = $params['start_stop'] ?? '';
            $trip_param = $params['trip'] ?? '';
            $end_stop = $params['end_stop'] ?? '';

            $result = [];

            if ($request_with_coords) {
                $latitude = $params['latitude'];
                $longitude = $params['longitude'];

                $num_stop = Trajet::getStopBefore($itinerary_json, $latitude, $longitude, 100, $id_in_geo);

                if ($num_stop === false) {
                    throw new \DomainException(json_encode(['error' => LANG_RESERVATION['reservation_pos_too_far']]));
                }

                $array_operation = [Arret::where("id_itineraire", "=", $id_itineraire)
                    ->where("num_arret", "=", $num_stop)
                    ->select([
                        'id_arret',
                        'nom_arret',
                        'num_arret',
                        'latitude',
                        'longitude'
                    ])->first()->toArray()];
            } else {
                //Getting the data
                $array_operation = Arret::where('id_itineraire', '=', $id_itineraire)
                    ->select([
                        'id_arret',
                        'nom_arret',
                        'num_arret',
                        'latitude',
                        'longitude'
                    ])
                    ->orderBy('num_arret')
                    ->get()->toArray();

                array_pop($array_operation); // On ne démarre jamais du terminus
            }

            $arret_itineraire = Arret::where('id_itineraire', '=', $id_itineraire)
                ->select([
                    'id_arret as id_stop',
                    'nom_arret as name_stop',
                    'num_arret as num_stop',
                    'latitude',
                    'longitude'
                ])
                ->orderBy('num_arret')
                ->get()->toArray();

            $free_places_max = -1;

            $array_operation = array_reduce($array_operation, function ($res, $item) use ($request_with_coords, &$free_places_max, $nb_place, $travel_alone, $date_depart, $id_itineraire) {

                if ($request_with_coords) {
                    $nowdate = date("Y-m-d", strtotime("now"));

                    $arr_id_trip = [];
                    foreach (Trajet::where("id_itineraire", "=", $id_itineraire)->get()->all() as $t) {
                        if ($t->is_valid_for_day(strtotime($nowdate))) {
                            $ha = Horaire_arret::where('id_trajet', "=", $t->id_trajet)->where("id_arret", "=", $item["id_arret"])->first();
                            $datetimeha = strtotime($nowdate . " " . $ha->heure_passage);
                            if ($datetimeha > strtotime("now") &&
                                $datetimeha < strtotime("now +2 hours")) {
                                $arr_id_trip[] = $t->id_trajet;
                            }
                        }
                    }
                    if (count($arr_id_trip) == 0)
                        throw new \DomainException(json_encode(['error' => LANG_RESERVATION["reservation_not_soon"]]));
                    $trips = Trajet::where('id_itineraire', '=', $id_itineraire)
                        ->join('horaire_arret', 'trajet.id_trajet', 'horaire_arret.id_trajet')
                        ->select(
                            [
                                'trajet.id_trajet as id_trajet',
                                'heure_passage as hour'
                            ])
                        ->where('id_arret', $item['id_arret'])
                        ->whereIn("trajet.id_trajet", $arr_id_trip)
                        ->get()->toArray();
                } else {
                    $trips = Trajet::where('id_itineraire', '=', $id_itineraire)
                        ->join('horaire_arret', 'trajet.id_trajet', 'horaire_arret.id_trajet')
                        ->select(
                            [
                                'trajet.id_trajet as id_trajet',
                                'heure_passage as hour'
                            ])
                        ->where('id_arret', $item['id_arret'])
                        ->get()->toArray();
                }


                $num_start_stop = $item['num_arret'];
                $item['trip'] = array_reduce($trips, function ($res, $item) use ($id_itineraire, &$free_places_max, $travel_alone, $nb_place, $num_start_stop, $date_depart) {
                    $trip = Trajet::where("id_trajet", "=", $item['id_trajet'])
                        ->select([
                            'id_itineraire',
                            'repetition',
                            'date_depart',
                            'id_trajet',
                            'place_max'
                        ])->first();

                    if ($trip->is_valid_for_day(strtotime($date_depart)) && strtotime(date("Y-m-d", strtotime("now"))) <= strtotime($date_depart)) {

                        $trip = $trip->toArray();
                        $end_stops = Arret::where('id_itineraire', '=', $trip['id_itineraire'])
                            ->where('num_arret', '>', $num_start_stop)
                            ->select([
                                'id_arret',
                                'nom_arret',
                                'num_arret'
                            ])
                            ->orderBy('num_arret')
                            ->get()->toArray();
                        $id_trajet = $trip['id_trajet'];
                        $place_max = $trip['place_max'];

                        $stops_before_start_stop = array_column(Arret::where('id_itineraire', '=', $id_itineraire)
                            ->where("num_arret", "<=", $num_start_stop)->get()->toArray(), "id_arret");
                        $stops_after_start_stop = array_column(Arret::where('id_itineraire', '=', $id_itineraire)
                            ->where("num_arret", ">", $num_start_stop)->get()->toArray(), "id_arret");
                        $taken_places_start_stop = Participe_a_trajet::where('id_trajet', "=", $id_trajet)
                            ->where(function ($q) use ($stops_before_start_stop, $stops_after_start_stop) {
                                $q->whereIn('id_arret_depart', $stops_before_start_stop)
                                    ->wherein('id_arret_fin', $stops_after_start_stop);
                            })
                            ->where("date_participation", "=", $date_depart)
                            ->sum('nb_places');
                        $path_possible = ($taken_places_start_stop == 0 && $travel_alone || !$travel_alone) && $place_max - $taken_places_start_stop >= $nb_place;
                        if ($path_possible && $free_places_max <= $place_max - $taken_places_start_stop) {
                            $free_places_max = $place_max - $taken_places_start_stop;
                        }
                        $process_arret = function ($res, $item) use ($date_depart, $id_itineraire, &$free_places_max, $nb_place, $place_max, $travel_alone, $id_trajet, &$path_possible) {
                            if ($path_possible) {
                                $stops_before_end_stop = array_column(Arret::where('id_itineraire', '=', $id_itineraire)
                                    ->where("num_arret", "<", $item['num_arret'])->get()->toArray(), "id_arret");
                                $stops_after_end_stop = array_column(Arret::where('id_itineraire', '=', $id_itineraire)
                                    ->where("num_arret", ">=", $item['num_arret'])->get()->toArray(), "id_arret");

                                $item['taken_places'] = Participe_a_trajet::where('id_trajet', "=", $id_trajet)
                                    ->where(function ($q) use ($stops_after_end_stop, $stops_before_end_stop) {
                                        $q->whereIn('id_arret_depart', $stops_before_end_stop)
                                            ->wherein('id_arret_fin', $stops_after_end_stop);
                                    })
                                    ->where("date_participation", "=", $date_depart)
                                    ->sum('nb_places');

                                $item['hour'] = Horaire_arret::where("id_arret", '=', $item["id_arret"])
                                    ->where("id_trajet", '=', $id_trajet)
                                    ->first()->heure_passage;


                                if (($item['taken_places'] == 0 && $travel_alone || !$travel_alone) && $place_max - $item['taken_places'] >= $nb_place) {
                                    $item['free_places'] = $place_max - $item['taken_places'];
                                    if ($free_places_max <= $place_max - $item['taken_places']) {
                                        $free_places_max = $place_max - $item['taken_places'];
                                    }
                                    $res[] = $item;
                                } else {
                                    $path_possible = false;
                                }
                            }
                            return $res;
                        };
                        $end_stops = array_reduce($end_stops, $process_arret, []);
                        if (count($end_stops) > 0) {
                            $item['end_stops'] = $end_stops;
                        }
                    }
                    if (isset($item['end_stops']) && count($item['end_stops']) > 0) {
                        $res[] = $item;
                    }
                    return $res;
                }, []);
                if (count($item['trip']) > 0) {
                    $res[] = $item;
                }
                return $res;
            }, []);


            //BUILDING THE RESULT ARRAY
            if (count($array_operation) > 0) {
                $result['stops_itinerary'] = $arret_itineraire;
                $result['free_places_max'] = $free_places_max;

                foreach ($array_operation as $item) {
                    $start_stop_item["id_stop"] = $item["id_arret"];
                    $start_stop_item["name_stop"] = $item["nom_arret"];
                    $start_stop_item["num_stop"] = $item["num_arret"];
                    $result['start_stops'][] = $start_stop_item;
                }
                if (strlen($start_stop) == 0 || !in_array($start_stop, array_column($array_operation, "id_arret"))) {
                    $pos_start_stops = 0;
                } else {
                    $pos_start_stops = array_search($start_stop, array_column($array_operation, "id_arret"));
                }
                foreach ($array_operation[$pos_start_stops]["trip"] as $item) {
                    $hour_trip_item["id_trip"] = $item["id_trajet"];
                    $hour_trip_item["hour_start"] = date(LANG_DATE["format_hour"], strtotime($item["hour"]));
                    $result['hour_trip'][] = $hour_trip_item;
                }
                if (strlen($trip_param) == 0 || !in_array($trip_param, array_column($array_operation[$pos_start_stops]["trip"], "id_trajet"))) {
                    $pos_trip_param = 0;
                } else {
                    $pos_trip_param = array_search($trip_param, array_column($array_operation[$pos_start_stops]["trip"], "id_trajet"));
                }
                foreach ($array_operation[$pos_start_stops]["trip"][$pos_trip_param]["end_stops"] as $item) {
                    $end_stop_item["id_stop"] = $item["id_arret"];
                    $end_stop_item["name_stop"] = $item["nom_arret"];
                    $end_stop_item["hour_end"] = date(LANG_DATE["format_hour"], strtotime($item["hour"]));
                    $end_stop_item['free_places'] = $item['free_places'];
                    $end_stop_item["num_stop"] = $item["num_arret"];
                    $result['end_stops'][] = $end_stop_item;
                }
                if (strlen($end_stop) == 0 || !in_array($end_stop, array_column($array_operation[$pos_start_stops]["trip"][$pos_trip_param]["end_stops"], "id_arret"))) {
                    $pos_end_stop = count($array_operation[$pos_start_stops]["trip"][$pos_trip_param]["end_stops"]) - 1;
                } else {
                    $pos_end_stop = array_search($end_stop, array_column($array_operation[$pos_start_stops]["trip"][$pos_trip_param]["end_stops"], "id_arret"));
                }

                $result = [
                    "success" => LANG_DATA['data_generated_success'],
                    "result" => [
                        "fields" => $result,
                        "selected" => [
                            "start_stops" => $pos_start_stops,
                            "hour_trip" => $pos_trip_param,
                            "end_stop" => $pos_end_stop
                        ]
                    ]
                ];

                if (isset($id_in_geo)) {
                    $result["result"]["selected"]["id_in_geo"] = $id_in_geo;
                }
            } else {
                throw new \DomainException(json_encode(['error' => LANG_DATA['data_not_found']]));
            }
            //}

            if ($give_coords) {
                try {
                    $result['result']['fields']['itinerary_geo'] = $itinerary_json;
                } catch (Exception $e) {
                    throw new \DomainException(json_encode(['error' => LANG_DATA['data_not_found']]));
                }
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
     * Handles POST requests on /mobile/reservation
     *
     * @param Request $req the PSR-7 Request object which originates from the HTTP request
     *
     * @param Response $resp
     *
     * @param array<mixed> $args
     *
     * @return Response
     */
    public static function reservation_post(Request $req, Response $resp, $args)
    {
        $params = (array)$req->getParsedBody();

        try {

            $token = $req->getHeaderLine('token');

            if (strlen($token) == 0)
                throw new \DomainException(json_encode(['error' => LANG_DATA['token_missing']]));

            if (!isset($params['id_itinerary']))
                throw new \DomainException(json_encode(['error' => LANG_DATA["incorrect_id"]]));
            $id_itinerary = $params['id_itinerary'];

            if (!isset($params['date']))
                throw new \DomainException(json_encode(['error' => LANG_RESERVATION["reservation_error"]]));
            $date_reservation = $params['date'];

            if (!isset($params['id_trip']))
                throw new \DomainException(json_encode(['error' => LANG_DATA["incorrect_id"]]));
            $id_trip = $params['id_trip'];
            $trip = Trajet::find($id_trip);

            if (isset($params['start_stop'])) {
                $request_with_coords = false;

                $id_start_stop = $params['start_stop'];
                $start_stop = Arret::find($id_start_stop);

            } else if (isset($params['latitude']) && isset($params['longitude'])) {
                $request_with_coords = true;

                $latitude = $params['latitude'];
                $longitude = $params['longitude'];
                $coords = Arret::where('id_itineraire', '=', $id_itinerary)
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
                $num_stop = Trajet::getStopBefore($itinerary_json, $latitude, $longitude, 100, $id_in_geo);

                if ($num_stop === false) {
                    throw new \DomainException(json_encode(['error' => LANG_RESERVATION['reservation_pos_too_far']]));
                }

                $start_stop = Arret::where('id_itineraire', '=', $id_itinerary)
                    ->where('num_arret', '=', $num_stop)->first();

                $id_start_stop = $start_stop->id_arret;

            } else {
                throw new \DomainException(json_encode(['error' => LANG_RESERVATION["reservation_error"]]));
            }


            if (!$trip->is_valid_for_day(strtotime($date_reservation)) || strtotime(date("Y-m-d", strtotime("now"))) > strtotime($date_reservation))
                throw new \DomainException(json_encode(['error' => LANG_RESERVATION["reservation_error"]]));


            $hour = Horaire_arret::where("id_arret", '=', $id_start_stop)
                ->where("id_trajet", '=', $id_trip)
                ->first();
            if (isset($hour->heure_passage)) {
                $hour_str = $hour->heure_passage;
                if (strtotime(date("H:i:s", strtotime("now"))) >= strtotime($date_reservation . " " . $hour_str))
                    throw new \DomainException(json_encode(['error' => LANG_RESERVATION["reservation_error"]]));
            } else {
                throw new \DomainException(json_encode(['error' => LANG_RESERVATION["reservation_error"]]));
            }
            if ($request_with_coords) {
                $nowdate = date("Y-m-d", strtotime("now"));
                $datetimeha = strtotime($nowdate . " " . $hour->heure_passage);
                if ($datetimeha <= strtotime("now") ||
                    $datetimeha >= strtotime("now +2 hours")) {
                    throw new \DomainException(json_encode(['error' => LANG_RESERVATION["reservation_error"]]));
                }
            }


            if (!isset($params['end_stop']))
                throw new \DomainException(json_encode(['error' => LANG_RESERVATION["reservation_error"]]));
            $id_end_stop = $params['end_stop'];

            if (!isset($params['travel_alone']))
                throw new \DomainException(json_encode(['error' => LANG_RESERVATION["reservation_error"]]));
            $travel_alone = $params['travel_alone'];

            if (!isset($params['nb_place']))
                throw new \DomainException(json_encode(['error' => LANG_RESERVATION["reservation_error"]]));
            $nb_place = $params['nb_place'];

            $user = Utilisateur::where('token', '=', $token)->first();
            if (!$user)
                throw new \DomainException(json_encode(['error' => LANG_DATA["bad_token"]]));

            if ($nb_place > $trip->place_max)
                throw new \DomainException(json_encode(['error' => LANG_RESERVATION["reservation_error"]]));

            $pat = Participe_a_trajet::where("id_trajet", "=", $id_trip)
                ->where("id_utilisateur", "=", $user->id_utilisateur)
                ->where("date_participation", "=", $date_reservation)
                ->first();
            if ($pat)
                throw new \DomainException(json_encode(['error' => LANG_RESERVATION["reservation_duplicate"]]));

            if ($travel_alone)
                $nb_place = $trip->place_max;

            $end_stops = Arret::where('id_itineraire', '=', $trip['id_itineraire'])
                ->where('num_arret', '>', $start_stop->num_arret)
                ->orderBy('num_arret')
                ->get()->toArray();

            $stops_before_start_stop = array_column(Arret::where('id_itineraire', '=', $id_itinerary)
                ->where("num_arret", "<=", $start_stop->num_arret)->get()->toArray(), "id_arret");
            $stops_after_start_stop = array_column(Arret::where('id_itineraire', '=', $id_itinerary)
                ->where("num_arret", ">", $start_stop->num_arret)->get()->toArray(), "id_arret");
            $taken_places_start_stop = Participe_a_trajet::where('id_trajet', "=", $id_trip)
                ->where(function ($q) use ($stops_before_start_stop, $stops_after_start_stop) {
                    $q->whereIn('id_arret_depart', $stops_before_start_stop)
                        ->wherein('id_arret_fin', $stops_after_start_stop);
                })
                ->where("date_participation", "=", $date_reservation)
                ->sum('nb_places');
            $place_max = $trip->place_max;
            $path_possible = ($taken_places_start_stop == 0 && $travel_alone || !$travel_alone) && $place_max - $taken_places_start_stop >= $nb_place;
            $stop_atteint = false;
            foreach ($end_stops as $item) {
                if ($path_possible && $stop_atteint) {
                    $stops_before_end_stop = array_column(Arret::where('id_itineraire', '=', $id_itinerary)
                        ->where("num_arret", "<", $item['num_arret'])->get()->toArray(), "id_arret");
                    $stops_after_end_stop = array_column(Arret::where('id_itineraire', '=', $id_itinerary)
                        ->where("num_arret", ">=", $item['num_arret'])->get()->toArray(), "id_arret");

                    $taken_places_end_stop = Participe_a_trajet::where('id_trajet', "=", $id_trip)
                        ->where(function ($q) use ($stops_after_end_stop, $stops_before_end_stop) {
                            $q->whereIn('id_arret_depart', $stops_before_end_stop)
                                ->wherein('id_arret_fin', $stops_after_end_stop);
                        })
                        ->where("date_participation", "=", $date_reservation)
                        ->sum('nb_places');

                    $item['hour'] = Horaire_arret::where("id_arret", '=', $item["id_arret"])
                        ->where("id_trajet", '=', $id_trip)
                        ->first()->heure_passage;


                    echo $item["id_arret"];
                    if (($taken_places_end_stop == 0 && $travel_alone || !$travel_alone) && $place_max - $taken_places_end_stop >= $nb_place) {
                        if ($item["id_arret"] == $id_end_stop) {
                            $stop_atteint = true;
                        }
                    } else {
                        $path_possible = false;
                    }
                } else {
                    break;
                }
            }

            if (!$path_possible)
                throw new \DomainException(json_encode(['error' => LANG_RESERVATION["reservation_error"]]));


            $pat = new Participe_a_trajet();
            $pat->id_utilisateur = $user->id_utilisateur;
            $pat->id_trajet = $id_trip;
            $pat->date_participation = $date_reservation;
            $pat->id_arret_depart = $id_start_stop;
            $pat->id_arret_fin = $id_end_stop;
            $pat->nb_places = $nb_place;

            if ($request_with_coords) {
                $pat->latitude_utilisateur = $latitude;
                $pat->longitude_utilisateur = $longitude;
            }

            if ($pat->save()) {
                $result = [
                    'success' => LANG_RESERVATION['reservation_success'],
                    'result' => [
                        'id_reservation' => $pat->id_reservation
                    ]
                ];
            } else {
                throw new \DomainException(json_encode(['error' => LANG_RESERVATION["reservation_error"]]));
            }

        } catch (\DomainException $e) {

            $resp->getBody()->write($e->getMessage());
            return $resp->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
        $resp->getBody()->write(json_encode(Utils::array_map_recursive('strval', $result), JSON_FORCE_OBJECT));
        return $resp->withHeader('Content-Type', 'application/json');
    }


    /**
     * Handles POST requests on /mobile/reservation-cancel
     *
     * @param Request $req the PSR-7 Request object which originates from the HTTP request
     *
     * @param Response $resp
     *
     * @param array<mixed> $args
     *
     * @return Response
     */
    public static function reservation_cancel_post(Request $req, Response $resp, $args)
    {
        $params = (array)$req->getParsedBody();

        try {

            $token = $req->getHeaderLine('token');

            if (strlen($token) == 0)
                throw new \DomainException(json_encode(['error' => LANG_DATA['token_missing']]));

            $user = Utilisateur::where('token', '=', $token)->first();

            if (!isset($params['id_reservation']))
                throw new \DomainException(json_encode(['error' => LANG_DATA["incorrect_id"]]));
            $id_reservation = $params['id_reservation'];


            $reserv = Participe_a_trajet::find($id_reservation);

            if ($reserv == null || $user->id_utilisateur != $reserv->id_utilisateur)
                throw new \DomainException(json_encode(['error' => LANG_RESERVATION["reservation_cancel_error"]]));


            if ($reserv->delete()) {
                $result = [
                    'success' => LANG_RESERVATION['reservation_cancelled']
                ];
            } else {
                throw new \DomainException(json_encode(['error' => LANG_RESERVATION["reservation_cancel_error"]]));
            }

        } catch (\DomainException $e) {

            $resp->getBody()->write($e->getMessage());
            return $resp->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
        $resp->getBody()->write(json_encode(Utils::array_map_recursive('strval', $result), JSON_FORCE_OBJECT));
        return $resp->withHeader('Content-Type', 'application/json');
    }

}

?>
