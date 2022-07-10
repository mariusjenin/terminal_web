<?php

namespace Terminal\Controller\MobileAppController;

use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Terminal\Model\Horaire_arret;
use Terminal\Model\Itineraire;
use Terminal\Model\Trajet;
use Terminal\Utils\Utils;

class ItineraryMobileController
{


    /**
     * Handles GET requests on /mobile/itineraries
     *
     * @note Give JSON corresponding to all itineraries of the app
     *
     * @param Request $req the PSR-7 Request object which originates from the HTTP request
     *
     * @param Response $resp
     *
     * @param array<mixed> $args
     *
     * @return Response
     */
    public static function itineraries_get(Request $req, Response $resp, $args)
    {
        $itineraries = Itineraire::select(['id_itineraire'])->get()->toArray();

        $stringify_id = function ($it) {
            $id = $it['id_itineraire'];
            $it = self::get_itinerary_details_array($id);
            return $it;
        };

        $itineraries = array_map($stringify_id, $itineraries);

        $result = [
            "success" => LANG_DATA['data_generated_success'],
            "result" => $itineraries
        ];


        $resp->getBody()->write(json_encode($result, JSON_FORCE_OBJECT));
        return $resp->withHeader('Content-Type', 'application/json');
    }


    /**
     * Handles GET requests on /mobile/itinerary/{id:.+}
     *
     * @param Request $req the PSR-7 Request object which originates from the HTTP request
     *
     * @param Response $resp
     *
     * @param array<mixed> $args
     *
     * @return Response
     */
    public static function itinerary_details_get(Request $req, Response $resp, $args)
    {
        try {
            $id = $args['id'] ?? 0;
            if ($id <= 0)
                throw new \DomainException(json_encode(['error' => LANG_DATA['incorrect_id']]));

            $itinerary = self::get_itinerary_details_array($id);

            $result = [
                "success" => LANG_DATA['data_generated_success'],
                "result" => $itinerary
            ];
        } catch (\DomainException $e) {
            // We use exceptions here to stop processing parameters as long as one does not satisfy a given
            // predicate.
            // The exception `$e` contains a valid JSON string that can be sent back to the application.

            $resp->getBody()->write($e->getMessage());
            return $resp->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }

        $resp->getBody()->write(json_encode(Utils::array_map_recursive('strval', $result)));
        return $resp->withHeader('Content-Type', 'application/json');
    }

    /**
     * Give array corresponding to the details of the itinerary with the given id
     * @param $id
     * @return array
     */
    public static function get_itinerary_details_array($id)
    {
        $process_trajet = function ($res, $arr) {
            $arret_horaires = Horaire_arret::where('id_trajet', '=', $arr['id_trajet'])
                ->join('arret', 'horaire_arret.id_arret', '=', 'arret.id_arret')
                ->select([
                    "num_arret as num_stop",
                    "heure_passage as time"
                ])
                ->orderBy('num_arret', 'asc')->get()->toArray();

            if (Trajet::find($arr['id_trajet'])->is_valid_for_month(strtotime("now"))) {
                $arret_horaires = array_map(function ($it) {
                    $it['num_stop'] = "" . $it['num_stop'];
                    $it['time'] = date(LANG_DATE["format_hour"], strtotime($it['time']));
                    return $it;
                }, $arret_horaires);


                $arr['time_passage'] = $arret_horaires;

                $arr['date_depart'] = date(LANG_DATE["format_date"], strtotime($arr['date_depart']));

                unset($arr['place_max']);
                unset($arr['id_itineraire']);
                unset($arr['id_trajet']);
                $res[] = $arr;
            }
            return $res;
        };

        $process_arret = function ($arr) {
            $arr['id_arret'] = "" . $arr['id_arret'];

            unset($arr['id_itineraire']);
            unset($arr['num_arret']);
            unset($arr['latitude']);
            unset($arr['longitude']);
            return $arr;
        };

        $itinerary = Itineraire::select([
            'id_itineraire',
            'nom_itineraire as name_itinerary'
        ])->find($id);

        $itinerary_clone = clone $itinerary;

        $itinerary = $itinerary->toArray();
        $itinerary['id_itineraire'] = "" . $itinerary['id_itineraire'];

        $itinerary["stops"] = array_map($process_arret, $itinerary_clone->arrets->toArray());
        $itinerary["trips"] = array_reduce($itinerary_clone->trajets->toArray(), $process_trajet, []);
        $id_itineray_item = array('id_itinerary' => $itinerary['id_itineraire']);
        $itinerary = $id_itineray_item + $itinerary;
        unset($itinerary['id_itineraire']);
        return $itinerary;
    }


}

?>
