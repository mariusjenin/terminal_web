<?php


namespace Terminal\Utils;

/**
 * Class Utils
 * @package Terminal\Utils
 */
class Utils
{
    /**
     * A very simple function used to automatically determine which hash algorithm
     * to use depending on whether a specific PHP installation supports it, or not.
     *
     * It also hashes the password given using the determined algorithm.
     *
     * @param string $pass The password to hash
     *
     * @return string The hashed password. Note that it is unnecessary to return the algorithm used
     *                because PHP's `password_verify` can infer it
     */
    public static function hash($pass)
    {
        // if our PHP supports it, use Argon2ID, else Argon2I if not,
        // and fallback to BCrypt if Argon2I isn't supported either.
        if (defined('PASSWORD_ARGON2ID'))
            return password_hash($pass, PASSWORD_ARGON2ID);
        else if (defined('PASSWORD_ARGON2I'))
            return password_hash($pass, PASSWORD_ARGON2I);
        else
            return password_hash($pass, PASSWORD_BCRYPT);
    }

    /**
     * Verify that a password is equal to a hash made with self::hash
     *
     * @param string $pwd password to verify
     * @param string $hash_pwd password to compare
     * @return bool
     */
    public static function verify_hash($pwd, $hash_pwd)
    {
        // `password_verify` is able to infer the hashing algorithm that was used by `self::hash` when registering
        return password_verify($pwd, $hash_pwd);
    }

    public static function generateToken()
    {
        return substr(base64_encode(openssl_random_pseudo_bytes(128)), 0, 64);
    }

    public static function array_map_recursive($callback, $array)
    {
        $func = function ($item) use (&$func, &$callback) {
            return is_array($item) ? array_map($func, $item) : call_user_func($callback, $item);
        };

        return array_map($func, $array);
    }

    /**
     * Calculates the great-circle distance between two points, with
     * the Haversine formula.
     * @param float $latitudeFrom Latitude of start point in [deg decimal]
     * @param float $longitudeFrom Longitude of start point in [deg decimal]
     * @param float $latitudeTo Latitude of target point in [deg decimal]
     * @param float $longitudeTo Longitude of target point in [deg decimal]
     * @param float $earthRadius Mean earth radius in [m]
     * @return float Distance between points in [m] (same as earthRadius)
     */
    public static function haversineGreatCircleDistance(
        $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
    {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }

    /**
     * Donne le jour dans la langue choisie
     * @param $n
     * @return mixed|string
     */
    public static function getDayString($n)
    {
        switch ($n) {
            case 1:
                return LANG_DATE["monday"];
            case 2:
                return LANG_DATE["tuesday"];
            case 3:
                return LANG_DATE["wednesday"];
            case 4:
                return LANG_DATE["thursday"];
            case 5:
                return LANG_DATE["friday"];
            case 6:
                return LANG_DATE["saterday"];
            case 7:
                return LANG_DATE["sunday"];
        }
        return "";
    }
}