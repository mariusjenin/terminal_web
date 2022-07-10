<?php


namespace Terminal\Controller\WebAppController;


class FileWebController
{
    public static function js($req, $resp, $args)
    {
        $resp->getBody()->write(file_get_contents("../js/" . $args['routes'] . ""));
        return $resp->withHeader('Content-Type', 'text/javascript');
    }


    public static function img($req, $resp, $args)
    {
        echo file_get_contents("../img/" . $args['routes'] . "");
        return $resp;
    }


    public static function fonts($req, $resp, $args)
    {
        echo file_get_contents("../fonts/" . $args['routes'] . "");
        return $resp;
    }


    public static function css($req, $resp, $args)
    {
        $resp->getBody()->write(file_get_contents("../css/" . $args['routes'] . ""));
        return $resp->withHeader('Content-Type', 'text/css');
    }

}