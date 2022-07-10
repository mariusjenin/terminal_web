<?php


namespace Terminal\Manager;

include_once __DIR__ . '/../../config/config.php';

/**
 * Manager se chargeant de faire les requêtes vers l'API pour récupérer les données géographiques
 * Class DirectionManager
 * @package Terminal\Manager
 */
class DirectionManager
{

    private static $_instance;

    private function __construct()
    {

    }

    public static function getInstance(): DirectionManager
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new DirectionManager();
        }
        return self::$_instance;
    }

    private static $URL_DIRECTION = "https://api.openrouteservice.org/v2/directions/driving-car/geojson";

    /**
     * Donne en array l'itinéraire passant par les points donnés en paramètre (avec les détails des segments s'ils sont demandés)
     * @param $coords
     * @param bool $add_segments
     * @return array
     * Array renvoyé :
     *  [
     *      "timestamp" => TIMESTAMP,
     *      "request" => [COORDS_REQUEST_1, COORDS_REQUEST_2, ..., COORDS_REQUEST_M],
     *      "response" => [
     *          "distance" => TOTAL_DISTANCE,
     *          "duration" => TOTAL_DURATION,
     *          "coordinates" => [COORDS_RESPONSE_1, COORDS_RESPONSE_2, ..., COORDS_RESPONSE_N],
     *          ("segments" => [INFOS_SEGMENT_1, INFOS_SEGMENT_1, ..., INFOS_SEGMENT_P],)
     *      ]
     *  ]
     *  Avec INFOS_SEGMENT_K :
     *  [
     *      "distance" => DISTANCE_ALL_STEPS,
     *      "duration" => DURATION_ALL_STEPS,
     *      "steps" => [
     *          [
     *              "distance" => DISTANCE_STEP,
     *              "duration" => DURATION_STEP,
     *              "type" => TYPE,
     *              "instruction" => INSTRUCTION,
     *              "name" => NAME,
     *              "way_points" => [INDEX_BEGINNING, INDEX_ENDING],
     *          ],
     *          [...], ..., [...]
     *      ]
     *  ]
     * @throws \Exception
     */
    public function itinerary_as_array(array $coords, $add_segments = false)
    {
        $param = [
            "coordinates" => $coords,
            "elevation" => "false",
            "maneuvers" => "false"
        ];

        $param["instructions"] = $add_segments ? "true" : "false";

        $http_header = [
            "Accept: application/json, application/geo+json, application/gpx+xml, img/png; charset=utf-8",
            "Authorization: " . TOKEN_OPENROUTESERVICE,
            "Content-Type: application/json; charset=utf-8"
        ];
        $crm = new CurlRequestManager();
        $result = $crm->post(self::$URL_DIRECTION, json_encode($param), $http_header);

        $result_to_array = json_decode($result, true);

        if (is_array($result_to_array) && !isset($result_to_array["error"])) {
            $rearranged_result_array = [
                "timestamp" => $result_to_array["metadata"]["timestamp"],
                "request" => $result_to_array["metadata"]["query"]["coordinates"],
                "response" => [
                    "distance" => $result_to_array["features"][0]["properties"]["summary"]["distance"],
                    "duration" => $result_to_array["features"][0]["properties"]["summary"]["duration"],
                    "coordinates" => $result_to_array["features"][0]["geometry"]["coordinates"]
                ],
            ];
            if ($add_segments) {
                $segments = array_reduce($result_to_array["features"][0]["properties"]["segments"], function ($arr, $item) {
                    $steps = $item["steps"];
                    $segment["start_stop_pos"] = $steps[0]["way_points"][0];
                    $segment["end_stop_pos"] = $steps[count($steps) - 1]["way_points"][1];
                    $segment["distance"] = $item["distance"];
                    $segment["duration"] = $item["duration"];
                    $arr[] = $segment;
                    return $arr;
                }, []);

                $rearranged_result_array["response"]["segments"] = $segments;
            }

            return $rearranged_result_array;
        } else {
            throw new \Exception(json_encode(["error" => $result_to_array["error"]["message"]]));
        }
    }
}