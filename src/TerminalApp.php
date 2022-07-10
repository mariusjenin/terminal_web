<?php


namespace Terminal;

use DI\Container;
use Selective\BasePath\BasePathMiddleware;
use Slim\Factory\AppFactory;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Terminal\Manager\CurlRequestManager;
use Terminal\Manager\RedirectManager;
use Terminal\Middleware\LangMiddleware;
use Terminal\Middleware\RedirectIfAdminConnectedMiddleware;
use Terminal\Middleware\RedirectIfAdminNotConnectedMiddleware;

/**
 * Classe qui se charge du routage de l'application web Vroum
 *
 * Class TerminalApp
 *
 * @package Vroum
 */
class TerminalApp
{
    private static $_instance;

    private $app;

    private function __construct()
    {
        $container = new Container();
        AppFactory::setContainer($container);

        $app = AppFactory::create();
        // Add Slim routing middleware
        $app->addRoutingMiddleware();
        // To automatically parse PUT bodies
        $app->addBodyParsingMiddleware();

        // Set the base path to run the app in a subdirectory.
        // This path is used in urlFor().
        $app->add(new BasePathMiddleware($app));

        //Set a Lang middleware to load the right string contants
        $app->add(new LangMiddleware());

        $term_app = $this;

        // Define Custom Error Handler
        $notFoundhandler = function (
//            ServerRequestInterface $request,
//            Throwable $exception,
//            bool $displayErrorDetails,
//            bool $logErrors,
//            bool $logErrorDetails
        ) use ($term_app) {
            //On redirige la page vers l'url de redirection en cas de not found
            $rm = RedirectManager::getInstance();
            $url = $rm->getUrlRedirect();
            $response = $term_app->app->getResponseFactory()->createResponse();
            $routeParser = $term_app->app->getRouteCollector()->getRouteParser();
            return $response->withHeader('Location', $routeParser->urlFor($url["route"], $url["param"]));
        };

        // Add Error middleware
        $errorMiddleware = $app->addErrorMiddleware(true, true, true);
        // On enregistre un errorMiddleware pour catch les notFound
        $errorMiddleware->setErrorHandler(HttpNotFoundException::class, $notFoundhandler);


        $this->app = $app;
    }

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            $_SERVER["ROOT_PATH"] = __DIR__ . "/../Vroum";
            self::$_instance = new TerminalApp();
        }
        return self::$_instance;
    }

    public function siteURL()
    {
        $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ||
            $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $domainName = $_SERVER['HTTP_HOST'];
        return $protocol . $domainName;
    }

    /**
     * Routes du site
     */
    public function addRoutes()
    {

        $this->app->add(function ($request, $handler) {
            $response = $handler->handle($request);
            return $response
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
                ->withHeader('X-Content-Type-Options', 'nosniff');
        });


        $this->addRoutesAPIMobileApp();

        $this->addRoutesAdminWebApp();


    }

    /**
     * Routes de l'API Mobile (Les applications mobiles feront des requêtes sur ces routes)
     */
    public function addRoutesAPIMobileApp()
    {
        //########################## GET #########################

        //Get itineraries
        $this->app->get('/mobile/itineraries', 'Terminal\Controller\MobileAppController\ItineraryMobileController::itineraries_get')
            ->setName('mobile_itineraries_get');

        //Get details of an itinerary
        $this->app->get('/mobile/itinerary/{id:.+}', 'Terminal\Controller\MobileAppController\ItineraryMobileController::itinerary_details_get')
            ->setName('mobile_itinerary_details_get');

        //Get future trips
        $this->app->get('/mobile/future-trips', 'Terminal\Controller\MobileAppController\TripMobileController::future_trips_get')
            ->setName('mobile_future_trips_get');

        //Get trip details for trip reserved (Passenger)
        $this->app->get('/mobile/trip-reserved-details/{id_reservation:.+}', 'Terminal\Controller\MobileAppController\TripMobileController::trip_reserved_get')
            ->setName('mobile_trip_reserved_get');

        //Get trip driven details (Driver)
        $this->app->get('/mobile/trip-driven-details/{id_trip:.+}', 'Terminal\Controller\MobileAppController\TripMobileController::trip_driven_get')
            ->setName('mobile_trip_driven_get');

        //Get driver position
        $this->app->get('/mobile/trip-reserved-ongoing/{id_reservation:.+}', 'Terminal\Controller\MobileAppController\TripMobileController::trip_reserved_ongoing_get')
            ->setName('trip_reserved_ongoing_get');

        //######################### POST #########################

        //Sign up from mobile app
        $this->app->post('/mobile/sign-up', 'Terminal\Controller\MobileAppController\IdentificationMobileController::sign_up_post')
            ->setName('mobile_sign_up_post');

        //Sign in from mobile app
        $this->app->post('/mobile/sign-in', 'Terminal\Controller\MobileAppController\IdentificationMobileController::sign_in_post')
            ->setName('mobile_sign_in_post');

        //Sign in automatically
        $this->app->post('/mobile/sign-in-auto', 'Terminal\Controller\MobileAppController\IdentificationMobileController::sign_in_auto_post')
            ->setName('mobile_sign_in_auto_post');

        //Reservation ongoing
        $this->app->post('/mobile/reservation-ongoing', 'Terminal\Controller\MobileAppController\ReservationMobileController::reservation_ongoing_post')
            ->setName('mobile_reservation_ongoing_post');

        //Reservation
        $this->app->post('/mobile/reservation', 'Terminal\Controller\MobileAppController\ReservationMobileController::reservation_post')
            ->setName('mobile_reservation_post');

        //Driver position
        $this->app->post('/mobile/trip-driven-ongoing', 'Terminal\Controller\MobileAppController\TripMobileController::trip_driven_ongoing_post')
            ->setName('mobile_trip_driven_ongoing_post');

        //Cancel reservation
        $this->app->post('/mobile/reservation-cancel', 'Terminal\Controller\MobileAppController\ReservationMobileController::reservation_cancel_post')
            ->setName('mobile_reservation_cancel_post');

    }


    /**
     * Routes du site Administrateur
     */
    public function addRoutesAdminWebApp()
    {
        /*
         * Ajoute les routes de développement (Pas nécessaire pour le fonctionnement du site) (Voir la définition de la fonction plus bas)
         *
         *
         $this->addRoutesDev();
//         */

        //########################## GET #########################

        //Page de connexion
        $this->app->get('/login', 'Terminal\Controller\WebAppController\IdentificationWebController::login_get')->setName('web_login_get')
            ->add(new RedirectIfAdminConnectedMiddleware());

        //Page d'accueil
        $this->app->get('/home', 'Terminal\Controller\WebAppController\HomeWebController::home_get')->setName('web_home_get')
            ->add(new RedirectIfAdminNotConnectedMiddleware());

        //Page des itinéraires
        $this->app->get('/itineraries', 'Terminal\Controller\WebAppController\ItineraryWebController::itineraries_get')->setName('web_itineraries_get')
            ->add(new RedirectIfAdminNotConnectedMiddleware());

        //Page pour créer un itinéraire
        $this->app->get('/create-itinerary', 'Terminal\Controller\WebAppController\ItineraryWebController::create_itinerary_get')->setName('web_create_itinerary_get')
            ->add(new RedirectIfAdminNotConnectedMiddleware());

        //Page des trajets d'un itinéraire
        $this->app->get('/trips/{id_itinerary:.+}', 'Terminal\Controller\WebAppController\TripWebController::trips_get')->setName('web_trips_get')
            ->add(new RedirectIfAdminNotConnectedMiddleware());

        //Page de création d'un trajet
        $this->app->get('/create-trip/{id_itinerary:.+}', 'Terminal\Controller\WebAppController\TripWebController::create_trip_get')->setName('web_create_trip_get')
            ->add(new RedirectIfAdminNotConnectedMiddleware());

        //Page de modification d'un trajet
        $this->app->get('/edit-trip/{id_trip:.+}', 'Terminal\Controller\WebAppController\TripWebController::edit_trip_get')->setName('web_edit_trip_get')
            ->add(new RedirectIfAdminNotConnectedMiddleware());

        //Page de création d'un compte
        $this->app->get('/create-account', 'Terminal\Controller\WebAppController\UserWebController::create_account_get')->setName('web_create_account_get')
            ->add(new RedirectIfAdminNotConnectedMiddleware());

        //Page des utilisateurs
        $this->app->get('/users', 'Terminal\Controller\WebAppController\UserWebController::users_get')->setName('web_users_get')
            ->add(new RedirectIfAdminNotConnectedMiddleware());

        //######################### POST #########################

        //Post de connexion
        $this->app->post('/login', 'Terminal\Controller\WebAppController\IdentificationWebController::login_post')->setName('web_login_post')
            ->add(new RedirectIfAdminConnectedMiddleware());

        //Post de déconnexion
        $this->app->post('/logout', 'Terminal\Controller\WebAppController\IdentificationWebController::logout_post')->setName('web_logout_post')
            ->add(new RedirectIfAdminNotConnectedMiddleware());

        //Post pour ajax Itinerary as json
        $this->app->post('/itinerary-json', 'Terminal\Controller\WebAppController\ItineraryWebController::itinerary_json_post')->setName('web_itinerary_json_post')
            ->add(new RedirectIfAdminNotConnectedMiddleware());

        //Post pour ajax Itinerary as json
        $this->app->post('/create-itinerary', 'Terminal\Controller\WebAppController\ItineraryWebController::create_itinerary_post')->setName('web_create_itinerary_post')
            ->add(new RedirectIfAdminNotConnectedMiddleware());

        //Calcul des horaires pour la création d'un trajet
        $this->app->post('/hours-create-trip', 'Terminal\Controller\WebAppController\TripWebController::hours_create_trip_post')->setName('web_hours_create_trip_post')
            ->add(new RedirectIfAdminNotConnectedMiddleware());

        //Création d'un trajet
        $this->app->post('/create-trip', 'Terminal\Controller\WebAppController\TripWebController::create_trip_post')->setName('web_create_trip_post')
            ->add(new RedirectIfAdminNotConnectedMiddleware());

        //Modification d'un trajet
        $this->app->post('/edit-trip', 'Terminal\Controller\WebAppController\TripWebController::edit_trip_post')->setName('web_edit_trip_post')
            ->add(new RedirectIfAdminNotConnectedMiddleware());

        //Suppression d'un trajet
        $this->app->post('/delete-trip', 'Terminal\Controller\WebAppController\TripWebController::delete_trip_post')->setName('web_delete_trip_post')
            ->add(new RedirectIfAdminNotConnectedMiddleware());

        //Suppression d'un itinéraire
        $this->app->post('/delete-itinerary', 'Terminal\Controller\WebAppController\ItineraryWebController::delete_itinerary_post')->setName('web_delete_itinerary_post')
            ->add(new RedirectIfAdminNotConnectedMiddleware());

        //Création d'un compte
        $this->app->post('/create-account', 'Terminal\Controller\WebAppController\UserWebController::create_account_post')->setName('web_create_account_post')
            ->add(new RedirectIfAdminNotConnectedMiddleware());

        //Suppression d'un utilisateur
        $this->app->post('/delete-user', 'Terminal\Controller\WebAppController\UserWebController::delete_user_post')->setName('web_delete_user_post')
            ->add(new RedirectIfAdminNotConnectedMiddleware());

        //Suppression d'un utilisateur
        $this->app->post('/edit-password', 'Terminal\Controller\WebAppController\UserWebController::edit_password_post')->setName('web_edit_password_post')
            ->add(new RedirectIfAdminNotConnectedMiddleware());

        //########################## ROUTE GET POUR LES FICHIERS #########################

        //route pour les fichiers js
        $this->app->get('/js/{routes:.+}', 'Terminal\Controller\WebAppController\FileWebController::js')->setName('js');

        //Route pour les images
        $this->app->get('/img/{routes:.+}', 'Terminal\Controller\WebAppController\FileWebController::img')->setName('img');

        //Route pour les fonts
        $this->app->get('/fonts/{routes:.+}', 'Terminal\Controller\WebAppController\FileWebController::fonts')->setName('fonts');

        //Route pour le css
        $this->app->get('/css/{routes:.+}', 'Terminal\Controller\WebAppController\FileWebController::css')->setName('css');
    }



    /**
     * Ajoute les routes pour le developpement (aller sur la route /root pour voir les pages des tests et de design dev)
     */
/*
    public function addRoutesDev()
    {
        $this->app->get('/root', function (Request $request, Response $response) {

            $response->getBody()->write(
                '<h1>ROOT</h1>' .
                '<a style="display: block" href="designdevroot">Design Dev</a>' .
                '<a style="display: block" href="test">Test</a>'
            );
            return $response;
        })->setName('root');

        $term_app = $this;

        //test
        $this->app->get('/test', function (Request $request, Response $response) {
            $response->getBody()->write("<h1>TEST</h1>" .
                "<button onclick=\"window.location.href = '" . $request->getAttributes()['__basePath__'] . "/root';\">Return to ROOT</button>"
            );

            $response->getBody()->write(
                '<a style="display: block" href="test_sign_up">Test Sign up</a>' .
                '<a style="display: block" href="test_sign_in">Test Sign in</a>' .
                '<a style="display: block" href="test_itineraries">Test Itineraries</a>' .
                '<a style="display: block" href="test_itinerary_details">Test Itinerary details</a>' .
                '<a style="display: block" href="test_future_trips">Test Future trips</a>' .
                '<a style="display: block" href="test_reservation_ongoing">Test Reservation ongoing</a>' .
                '<a style="display: block" href="test_reservation_ongoing_pos">Test Reservation ongoing Pos</a>' .
                '<a style="display: block" href="test_itinerary_as_json">Test Itinerary as JSON</a>' .
                '<a style="display: block" href="test_reservation">Test Reservation</a>' .
                '<a style="display: block" href="test_reservation_pos">Test Reservation Pos</a>' .
                '<a style="display: block" href="test_trip_reserved">Test Trip reserved</a>' .
                '<a style="display: block" href="test_trip_driven">Test Trip driven</a>' .
                '<a style="display: block" href="test_haversine">Test Haversine</a>'.
                '<a style="display: block" href="test_trip_driven_ongoing">Test Trip driven ongoing</a>'.
                '<a style="display: block" href="test_trip_reserved_ongoing">Test Trip reserved ongoing</a>'.
                '<a style="display: block" href="test_reservation_cancel">Test Reservation cancel</a>'.
                '<a style="display: block" href="test_hours_create_trip">Test Hours create trip</a>'
            );
            return $response;
        })->setName('test');

        //test_sign_up
        $this->app->get('/test_sign_up', function (Request $request, Response $response) use ($term_app) {
            $response->getBody()->write("<h1>Test Sign up</h1>" .
                "<button onclick=\"window.location.href = '" . $request->getAttributes()['__basePath__'] . "/test';\">Return to TEST</button>"
            );


            $param = [
                "email" => "mariusjenin@gmail.com",
                "pwd" => "azerty",
                "pwd_confirm" => "azerty"
            ];
            $url = $term_app->siteURL() . $term_app->urlFor('mobile_sign_up_post');

            $result = CurlRequestManager::getInstance()->post($url, $param, []);

            ob_start();
            echo "<pre>";
            var_dump($url);
            var_dump($param);
            var_dump(json_decode($result));
            echo $result;
            echo "</pre>";
            $result = ob_get_clean();

            $response->getBody()->write($result);
            return $response;
        })->setName('test_sign_up');

        //test_sign_in
        $this->app->get('/test_sign_in', function (Request $request, Response $response) use ($term_app) {
            $response->getBody()->write("<h1>Test Sign in</h1>" .
                "<button onclick=\"window.location.href = '" . $request->getAttributes()['__basePath__'] . "/test';\">Return to TEST</button>"
            );


            $param = [
                "email" => "mariusjenin@gmail.com",
                "pwd" => "azerty"
            ];
            $url = $term_app->siteURL() . $term_app->urlFor('mobile_sign_in_post');

            $result = CurlRequestManager::getInstance()->post($url, $param, []);

            ob_start();
            echo "<pre>";
            var_dump($url);
            var_dump($param);
            var_dump(json_decode($result));
            echo $result;
            echo "</pre>";
            $result = ob_get_clean();

            $response->getBody()->write($result);
            return $response;
        })->setName('test_sign_in');

        //test_itineraries
        $this->app->get('/test_itineraries', function (Request $request, Response $response) use ($term_app) {
            $response->getBody()->write("<h1>Test Itineraries</h1>" .
                "<button onclick=\"window.location.href = '" . $request->getAttributes()['__basePath__'] . "/test';\">Return to TEST</button>"
            );

            $url = $term_app->siteURL() . $term_app->urlFor('mobile_itineraries_get');

            $result = CurlRequestManager::getInstance()->get($url, [], []);

            ob_start();
            echo "<pre>";
            var_dump($url);
            var_export(json_decode($result));
            echo $result;
            echo "</pre>";
            $result = ob_get_clean();

            $response->getBody()->write($result);
            return $response;
        })->setName('test_itineraries');

        //test_itinerary_details
        $this->app->get('/test_itinerary_details', function (Request $request, Response $response) use ($term_app) {
            $response->getBody()->write("<h1>Test Itinerary details</h1>" .
                "<button onclick=\"window.location.href = '" . $request->getAttributes()['__basePath__'] . "/test';\">Return to TEST</button>"
            );

            $url = $term_app->siteURL() . $term_app->urlFor('mobile_itinerary_details_get', ['id' => 2]);

            $result = CurlRequestManager::getInstance()->get($url, [], []);

            ob_start();
            echo "<pre>";
            var_dump($url);
            print_r(json_decode($result));
            echo $result;
            echo "</pre>";
            $result = ob_get_clean();

            $response->getBody()->write($result);
            return $response;
        })->setName('test_itinerary_details');

        //test_future_trips
        $this->app->get('/test_future_trips', function (Request $request, Response $response) use ($term_app) {
            $response->getBody()->write("<h1>Test Future trips</h1>" .
                "<button onclick=\"window.location.href = '" . $request->getAttributes()['__basePath__'] . "/test';\">Return to TEST</button>"
            );

            $url = $term_app->siteURL() . $term_app->urlFor('mobile_future_trips_get');

//            $result = CurlRequestManager::getInstance()->get($url, [], ["token: VRR0jGQuGS4UT7rUm4NmqTr40HbRjMJfILa2sqtnzx62T3oafMLzAXddeSGUIMBN"]);
            $result = CurlRequestManager::getInstance()->get($url, [], ["token: gvGpCj3kPXVTBTh6H8ljP+Pqxj7fDnQZIe7aWKL0C9ipFdKcVb2s36ecoUO3oRYG"]);

            ob_start();
            echo "<pre>";
            var_dump($url);
            var_dump(json_decode($result));
            echo $result;
            echo "</pre>";
            $result = ob_get_clean();

            $response->getBody()->write($result);
            return $response;
        })->setName('test_future_trips');

        //test_reservation_ongoing
        $this->app->get('/test_reservation_ongoing', function (Request $request, Response $response) use ($term_app) {
            $response->getBody()->write("<h1>Test Reservation ongoing</h1>" .
                "<button onclick=\"window.location.href = '" . $request->getAttributes()['__basePath__'] . "/test';\">Return to TEST</button>"
            );

            $url = $term_app->siteURL() . $term_app->urlFor('mobile_reservation_ongoing_post');

            $result = CurlRequestManager::getInstance()->post($url,
                [
                    "id_itinerary" => "2",
                    "give_coords" => "0",
                    "date" => date("Y-m-d", strtotime("12-07-2021")),
                    "nb_place" => "2",
                    "travel_alone" => "0",
                ], []);

            ob_start();
            echo "<pre>";
            var_dump($url);
            print_r(json_decode($result));
            echo $result;
            echo "</pre>";
            $result = ob_get_clean();

            $response->getBody()->write($result);
            return $response;
        })->setName('test_reservation_ongoing');

        //test_reservation_ongoing_pos
        $this->app->get('/test_reservation_ongoing_pos', function (Request $request, Response $response) use ($term_app) {
            $response->getBody()->write("<h1>Test Reservation ongoing Pos</h1>" .
                "<button onclick=\"window.location.href = '" . $request->getAttributes()['__basePath__'] . "/test';\">Return to TEST</button>"
            );

            $url = $term_app->siteURL() . $term_app->urlFor('mobile_reservation_ongoing_post');

            $result = CurlRequestManager::getInstance()->post($url,
                [
                    "id_itinerary" => "3",
                    "give_coords" => "0",
                    "date" => date("Y-m-d", strtotime("24-06-2021")),
                    "nb_place" => "2",
                    "travel_alone" => "0",
                    "latitude" => "48.658991526930926",
                    "longitude" => "6.157642769855889",
                    "end_stop" => "11",
                ], []);

            ob_start();
            echo "<pre>";
            var_dump($url);
            print_r(json_decode($result));
            echo $result;
            echo "</pre>";
            $result = ob_get_clean();

            $response->getBody()->write($result);
            return $response;
        })->setName('test_reservation_ongoing_pos');

        //test_itinerary_as_json
        $this->app->get('/test_itinerary_as_json', function (Request $request, Response $response) use ($term_app) {
            $response->getBody()->write("<h1>Test Itinerary as JSON</h1>" .
                "<button onclick=\"window.location.href = '" . $request->getAttributes()['__basePath__'] . "/test';\">Return to TEST</button>"
            );

            $coords = [[8.681495, 49.41461], [8.686507, 49.41943], [8.687872, 49.420318]];

            $result = DirectionManager::getInstance()->itinerary_as_array($coords, true);

            ob_start();
            echo "<pre>";
            var_dump($coords);
            print_r($result);
            echo "</pre>";
            $result = ob_get_clean();

            $response->getBody()->write($result);
            return $response;
        })->setName('test_itinerary_as_json');

        //test_reservation
        $this->app->get('/test_reservation', function (Request $request, Response $response) use ($term_app) {
            $response->getBody()->write("<h1>Test Reservation</h1>" .
                "<button onclick=\"window.location.href = '" . $request->getAttributes()['__basePath__'] . "/test';\">Return to TEST</button>"
            );

            $url = $term_app->siteURL() . $term_app->urlFor('mobile_reservation_post');

            $result = CurlRequestManager::getInstance()->post($url,
                [
                    "id_itinerary" => "3",
                    "id_trip" => "6",
                    "date" => date("Y-m-d", strtotime("30-06-2021")),
                    "start_stop" => "9",
                    "end_stop" => "11",
                    "travel_alone" => "0",
                    "nb_place" => "2"
                ],
                ["token: VRR0jGQuGS4UT7rUm4NmqTr40HbRjMJfILa2sqtnzx62T3oafMLzAXddeSGUIMBN"]);

            ob_start();
            echo "<pre>";
            var_dump($url);
            print_r(json_decode($result));
            echo $result;
            echo "</pre>";
            $result = ob_get_clean();

            $response->getBody()->write($result);
            return $response;
        })->setName('test_reservation');

        //test_reservation_pos
        $this->app->get('/test_reservation_pos', function (Request $request, Response $response) use ($term_app) {
            $response->getBody()->write("<h1>Test Reservation Pos</h1>" .
                "<button onclick=\"window.location.href = '" . $request->getAttributes()['__basePath__'] . "/test';\">Return to TEST</button>"
            );

            $url = $term_app->siteURL() . $term_app->urlFor('reservation_post');

            $result = CurlRequestManager::getInstance()->post($url,
                [
                    "id_itinerary" => "3",
                    "id_trip" => "6",
                    "date" => date("Y-m-d", strtotime("30-06-2021")),
                    "latitude" => "48.6588588298236",
                    "longitude" => "6.157948144418506",
                    "end_stop" => "11",
                    "travel_alone" => "0",
                    "nb_place" => "2"
                ],
                ["token: VRR0jGQuGS4UT7rUm4NmqTr40HbRjMJfILa2sqtnzx62T3oafMLzAXddeSGUIMBN"]);

            ob_start();
            echo "<pre>";
            var_dump($url);
            print_r(json_decode($result));
            echo $result;
            echo "</pre>";
            $result = ob_get_clean();

            $response->getBody()->write($result);
            return $response;
        })->setName('test_reservation_pos');

        //test_trip_reserved
        $this->app->get('/test_trip_reserved', function (Request $request, Response $response) use ($term_app) {
            $response->getBody()->write("<h1>Test Trip reserved</h1>" .
                "<button onclick=\"window.location.href = '" . $request->getAttributes()['__basePath__'] . "/test';\">Return to TEST</button>"
            );

            $url = $term_app->siteURL() . $term_app->urlFor('mobile_trip_reserved_get', ["id_reservation" => "2"]);

            $result = CurlRequestManager::getInstance()->get($url,
                [],
                ["token: VRR0jGQuGS4UT7rUm4NmqTr40HbRjMJfILa2sqtnzx62T3oafMLzAXddeSGUIMBN"]);

            ob_start();
            echo "<pre>";
            var_dump($url);
            print_r(json_decode($result));
            echo $result;
            echo "</pre>";
            $result = ob_get_clean();

            $response->getBody()->write($result);
            return $response;
        })->setName('test_trip_reserved');

        //test_trip_driven
        $this->app->get('/test_trip_driven', function (Request $request, Response $response) use ($term_app) {
            $response->getBody()->write("<h1>Test Trip driven</h1>" .
                "<button onclick=\"window.location.href = '" . $request->getAttributes()['__basePath__'] . "/test';\">Return to TEST</button>"
            );

            $url = $term_app->siteURL() . $term_app->urlFor('mobile_trip_driven_get', ["id_trip" => "6"]);

            $result = CurlRequestManager::getInstance()->get($url,
                [],
                ["token: gvGpCj3kPXVTBTh6H8ljP+Pqxj7fDnQZIe7aWKL0C9ipFdKcVb2s36ecoUO3oRYG"]);

            ob_start();
            echo "<pre>";
            var_dump($url);
            print_r(json_decode($result));
            echo $result;
            echo "</pre>";
            $result = ob_get_clean();

            $response->getBody()->write($result);
            return $response;
        })->setName('test_trip_driven');

        //test_haversine
        $this->app->get('/test_haversine', function (Request $request, Response $response) use ($term_app) {
            $response->getBody()->write("<h1>Test Haversine</h1>" .
                "<button onclick=\"window.location.href = '" . $request->getAttributes()['__basePath__'] . "/test';\">Return to TEST</button>"
            );


            $latFrom = 8.685299;
            $longFrom = 49.417736;
            $latTom = 8.68963;
            $longTo = 49.42051;
            $coords = [[8.681495, 49.41461], [8.686507, 49.41943], [8.687872, 49.420318]];

            $response->getBody()->write("<pre>" . Utils::haversineGreatCircleDistance($latFrom, $longFrom, $latTom, $longTo) .
                " mètres entre {" . $latFrom . ", " . $longFrom . "} et {" . $latFrom . ", " . $longFrom . "}</pre>");

            $result = DirectionManager::getInstance()->itinerary_as_array($coords, true);

            $id = 0;
            $dist = 6371000;

            ob_start();
            echo "<pre>";
            for ($i = 0; $i < count($result["response"]["coordinates"]); $i++) {
                $distLocal = Utils::haversineGreatCircleDistance($latFrom, $longFrom, $result["response"]["coordinates"][$i][0], $result["response"]["coordinates"][$i][1]);
                echo "\n" . $i . " " . $distLocal;
                if ($dist > $distLocal) {
                    $dist = $distLocal;
                    $id = $i;
                }
            }

            echo "\n\n" . $id . " | " . $dist;
            echo "</pre>";
            $result = ob_get_clean();

            $response->getBody()->write($result);

            return $response;
        })->setName('test_haversine');

        //test_trip_driven_ongoing
        $this->app->get('/test_trip_driven_ongoing', function (Request $request, Response $response) use ($term_app) {
            $response->getBody()->write("<h1>Test Trip driven ongoing</h1>" .
                "<button onclick=\"window.location.href = '" . $request->getAttributes()['__basePath__'] . "/test';\">Return to TEST</button>"
            );

            $url = $term_app->siteURL() . $term_app->urlFor('mobile_trip_driven_ongoing_post');

            $result = CurlRequestManager::getInstance()->post($url,
                [
                    "latitude" =>"48.6511204310114",
                    "longitude"=>"6.148884450433099",
                    "id_trip"=>"6"
                ],
                ["token: gvGpCj3kPXVTBTh6H8ljP+Pqxj7fDnQZIe7aWKL0C9ipFdKcVb2s36ecoUO3oRYG"]);

            ob_start();
            echo "<pre>";
            var_dump($url);
            print_r(json_decode($result));
            echo $result;
            echo "</pre>";
            $result = ob_get_clean();

            $response->getBody()->write($result);
            return $response;
        })->setName('test_trip_driven_ongoing');

        //test_trip_reserved_ongoing
        $this->app->get('/test_trip_reserved_ongoing', function (Request $request, Response $response) use ($term_app) {
            $response->getBody()->write("<h1>Test Trip reserved ongoing</h1>" .
                "<button onclick=\"window.location.href = '" . $request->getAttributes()['__basePath__'] . "/test';\">Return to TEST</button>"
            );

            $url = $term_app->siteURL() . $term_app->urlFor('trip_reserved_ongoing_get',["id_reservation" => "3"]);

            $result = CurlRequestManager::getInstance()->get($url,
                [],
                ["token: VRR0jGQuGS4UT7rUm4NmqTr40HbRjMJfILa2sqtnzx62T3oafMLzAXddeSGUIMBN"]);

            ob_start();
            echo "<pre>";
            var_dump($url);
            print_r(json_decode($result));
            echo $result;
            echo "</pre>";
            $result = ob_get_clean();

            $response->getBody()->write($result);
            return $response;
        })->setName('test_trip_reserved_ongoing');

        //test_reservation_cancel
        $this->app->get('/test_reservation_cancel', function (Request $request, Response $response) use ($term_app) {
            $response->getBody()->write("<h1>Test Reservation cancel</h1>" .
                "<button onclick=\"window.location.href = '" . $request->getAttributes()['__basePath__'] . "/test';\">Return to TEST</button>"
            );

            $url = $term_app->siteURL() . $term_app->urlFor('mobile_reservation_cancel_post');

            $result = CurlRequestManager::getInstance()->post($url,
                ["id_reservation" => "4"],
                ["token: VRR0jGQuGS4UT7rUm4NmqTr40HbRjMJfILa2sqtnzx62T3oafMLzAXddeSGUIMBN"]);

            ob_start();
            echo "<pre>";
            var_dump($url);
            print_r(json_decode($result));
            echo $result;
            echo "</pre>";
            $result = ob_get_clean();

            $response->getBody()->write($result);
            return $response;
        })->setName('test_reservation_cancel');

        //test_hours_create_trip
        $this->app->get('/test_hours_create_trip', function (Request $request, Response $response) use ($term_app) {
            $response->getBody()->write("<h1>Test Hours create trip</h1>" .
                "<button onclick=\"window.location.href = '" . $request->getAttributes()['__basePath__'] . "/test';\">Return to TEST</button>"
            );

            $url = $term_app->siteURL() . $term_app->urlFor('web_hours_create_trip_post');

            $result = CurlRequestManager::getInstance()->post($url,
                [
                    "time_start" => "14:00",
                    "id_itinerary" => "2"
                ],
                []);

            ob_start();
            echo "<pre>";
            var_dump($url);
            print_r(json_decode($result));
            echo $result;
            echo "</pre>";
            $result = ob_get_clean();

            $response->getBody()->write($result);
            return $response;
        })->setName('test_hours_create_trip');

        //designdev
        $this->app->get('/designdevroot', function (Request $request, Response $response) {
            $response->getBody()->write("<h1>DesignDev</h1>" .
                "<button onclick=\"window.location.href = '" . $request->getAttributes()['__basePath__'] . "/root';\">Return to ROOT</button>"
            );

            $dir = '../designdev';
            $files = scandir($dir);
            for ($i = 0; $i < count($files); $i++) {
                if ($files[$i] != '.' && $files[$i] != '..') {
                    $response->getBody()->write('<a style="display: block" href="designdev/' . $files[$i] . '">' . $files[$i] . '</a>');
                }
            }

            return $response;
        })->setName('designdev');

        //designdev
        $this->app->get('/designdev/{routes:.+}', function (Request $request, Response $response, $args) {
            echo file_get_contents("../designdev/" . $args['routes'] . "");
            return $response;
        })->setName('designdev_routes');

        //css
        $this->app->get('/designdev/css/{routes:.+}', function (Request $request, Response $response, $args) {
            $response->getBody()->write(file_get_contents("../css/" . $args['routes'] . ""));
            return $response->withHeader('Content-Type', 'text/css');
        });

        //js
        $this->app->get('/designdev/js/{routes:.+}', function (Request $request, Response $response, $args) {
            $response->getBody()->write(file_get_contents("../js/" . $args['routes'] . ""));
            return $response->withHeader('Content-Type', 'text/javascript');
        });

        //Route pour les images
        $this->app->get('/designdev/img/{routes:.+}', function (Request $request, Response $response, $args) {
            echo file_get_contents("../img/" . $args['routes'] . "");
            return $response;
        });

        //Route pour les fonts
        $this->app->get('/designdev/fonts/{routes:.+}', function (Request $request, Response $response, $args) {
            echo file_get_contents("../fonts/" . $args['routes'] . "");
            return $response;
        });

    }
*/
    /**
     * Lance l'app
     */
    public function run()
    {
        // Run app
        $this->app->run();
    }

    /**
     * Donne l'url de la route avec le nom donné avec les paramètres donnés
     * @param $route
     * @param array $args
     * @return string
     */
    public function urlFor($route, $args = [])
    {
        return $this->app->getRouteCollector()->getRouteParser()->urlFor($route, $args);
    }
}
