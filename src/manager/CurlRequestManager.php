<?php


namespace Terminal\Manager;

/**
 * Manager se chargeant d'effectuer des Requête Http avec Curl
 * Class CurlRequestManager
 * @package Terminal\Manager
 */
class CurlRequestManager
{
    private static $_instance;
    private static $TIMEOUT = 10;

    public static function getInstance(): CurlRequestManager
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new CurlRequestManager();
        }
        return self::$_instance;
    }


    function post($url, $post_fields, array $http_header)
    {

        $ch = curl_init();

        //URL
        curl_setopt($ch, CURLOPT_URL, $url);
        //Return instead of echo
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //Timeout
        curl_setopt($ch, CURLOPT_TIMEOUT, self::$TIMEOUT);
        //No HEADER
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //POST
        curl_setopt($ch, CURLOPT_POST, TRUE);
        //POST Fields
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        //HEADER of HTTP Request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_header);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    function get($url, $get_params, array $http_header)
    {

        $ch = curl_init();

        //URL
        curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($get_params));
        //Return instead of echo
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //Timeout
        curl_setopt($ch, CURLOPT_TIMEOUT, self::$TIMEOUT);
        //No HEADER
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //HEADER of HTTP Request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_header);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}