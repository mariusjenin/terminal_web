<?php


namespace Terminal\View;


use Terminal\Controller\WebAppController\GlobalWebController;
use Terminal\Model\Trajet;
use Terminal\Model\Utilisateur;
use Terminal\TerminalApp;
use Terminal\Utils\Utils;

class TripView
{

    public function __construct()
    {
    }

    /**
     * Renvoie l'html de la page des trajets d'un itinéraire
     * @return string
     */
    public function renderTrips($itineraire, array $trips)
    {
        $term = TerminalApp::getInstance();
        $gc = new GlobalWebController();
        $trips_of = LANG_TRIP["trips_of"];
        $see_all_itineraries = LANG_TRIP["see_all_itineraries"];
        $titleBold = $trips_of . " <b>" . $itineraire->nom_itineraire . "</b>";
        $title = $trips_of . " " . $itineraire->nom_itineraire;
        $res = $gc->head("Terminal - " . $title, [
            $term->urlFor('css', ["routes" => "custom/trips.css"])
        ]);
        $res .= <<<END
<div class="content scroll_page_container">
    <div class="scroll_page_navbar">
END;
        $res .= $gc->navbar();
        $res .= <<<END
        <div class="notch_bar">
            <div class="notch_title">
                <div>$titleBold</div>
            </div>
            <div class="notch_right">
                <a href="{$term->urlFor('web_itineraries_get')}"> 
                    <div class="btn_hoverable btn_in_notch d-flex justify-content-center align-items-center">
                        <img src="{$term->urlFor('img', ["routes" => "icons/enter.png"])}">
                    </div>
                </a>
                <div>{$see_all_itineraries}</div>
            </div>
        </div>
    </div>


    <div class="scroll_page_content">
        <div class="container mt-3">
            <div class="row p-1 p-sm-0">
                <div class="trips_box col-12 p-0 my-2">
END;


        $no_trips = LANG_TRIP["no_trips_for_itinerary"];
        if (count($trips) == 0) {
            $res .= <<<END
                    <div class="no_trips_in_itinerary d-flex flex-row align-items-center justify-content-center m-3">
                        {$no_trips}
                    </div>
END;
        } else {

            foreach ($trips as $t) {
                $repetition = "";
                switch ($t["repetition"]) {
                    case Trajet::OUVRES_REPETITION:
                        $repetition = "OUVRES";
                        break;
                    case Trajet::WEEKEND_REPETITION:
                        $repetition = "WEEKEND";
                        break;
                    case Trajet::UNIQUE_REPETITION:
                        $repetition = date(LANG_DATE["format_date"], strtotime($t["date_depart"]));
                        break;
                    case Trajet::HEBDOMADAIRE_REPETITION:
                        $repetition = "HEBDOMADAIRE - " . Utils::getDayString(date("N", strtotime($t["date_depart"])));
                        break;
                    case Trajet::MENSUEL_REPETITION:
                        $repetition = "MENSUEL - " . date("d", strtotime($t["date_depart"])) . "/__/__";
                        break;
                    case Trajet::ANNUEL_REPETITION:
                        $repetition = "ANNUEL - " . date("d/m", strtotime($t["date_depart"])) . "/__";
                        break;
                }

                $time_begin = date(LANG_DATE["format_hour"], strtotime($t["time_begin"]));
                $time_end = date(LANG_DATE["format_hour"], strtotime($t["time_end"]));
                $start = LANG_TRIP["start"];
                $end = LANG_TRIP["end"];
                $res .= <<<END
                    <div class="line_trip d-flex flex-row align-items-center justify-content-center m-3">
                         <a  href="{$term->urlFor('web_edit_trip_get', ["id_trip" => $t["id_trajet"]])}"
                             class="line_content_trip d-flex flex-row justify-content-around align-items-center px-3 py-2">
                                <div class="px-2">{$repetition}</div>
                                <div class="delimiter_trip_content"></div>
                                <div class="px-2">{$start} {$time_begin}</div>
                                <div class="delimiter_trip_content"></div>
                                <div class="px-2">{$end} {$time_end}</div>
                        </a>
                        <a href="{$term->urlFor('web_edit_trip_get', ["id_trip" => $t["id_trajet"]])}">
                            <div class="btn_trip">
                                <img src="{$term->urlFor('img', ["routes" => "icons/edit.png"])}">
                            </div>
                        </a>
                        <div data-modal_accept="{$term->urlFor('web_delete_trip_post')}" 
                        data-modal_accept_data='{ "id_trip": {$t["id_trajet"]}}'
                        class="btn_trip btn_delete">
                            <img src="{$term->urlFor('img', ["routes" => "icons/bin.png"])}">
                        </div>
                    </div>
END;
            }
        }

        $new_trip = LANG_TRIP["new_trip"];
        $res .= <<<END
                    <a href="{$term->urlFor('web_create_trip_get', ["id_itinerary" => $itineraire->id_itineraire])}">
                        <div class="new_trip d-flex flex-row align-items-center justify-content-center m-3">
                            <div class="add_trip d-flex justify-content-center mr-3"></div>
                            {$new_trip}
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
END;
        $res .= $gc->foot([$term->urlFor('js', ["routes" => "custom/trips.js"])]);
        return $res;
    }


    /**
     * Renvoie l'html de la page Création d'un trajet
     * @return string
     */
    public function renderCreateTrip($itineraire, $drivers, $trip = null)
    {
        $is_creation = $trip == null;
        $term = TerminalApp::getInstance();
        $gc = new GlobalWebController();
        if ($is_creation) {
            $texte = LANG_TRIP["new_trip_title"] . " ";
        } else {
            $texte = LANG_TRIP["edit_trip_title"] . " ";
        }
        $titleBold = $texte . "<b>" . $itineraire->nom_itineraire . "</b>";
        $title = $texte . $itineraire->nom_itineraire;
        $res = $gc->head("Terminal - " . $title, [
            $term->urlFor('css', ["routes" => "custom/create_edit_trip.css"])
        ]);
        $res .= <<<END
<div class="content no_scroll_page_container">
    <div class="no_scroll_page_navbar">
END;
        $res .= $gc->navbar();

        $date_today = date("Y-m-d", strtotime("now"));

        if ($is_creation) {
            $hour = date("H:i", strtotime("now"));
            $date = $date_today;
            $nb_place = 1;
            $email = "";
            $repetition = "OUVRES";
            $txt_btn = LANG_TRIP["create_this_trip"];
            $url_submit = $term->urlFor('web_create_trip_post');
            $input_hidden = "";
        } else {
            $hour = date("H:i", strtotime($trip["hour_trip"]["0"]["heure_passage"]));
            $date_depart = $trip["date_depart"];
            if ($date_depart != null) {
                $date = date("Y-m-d", strtotime($date_depart));
            } else {
                $date = date("Y-m-d", strtotime("now"));
            }
            $email = $trip["email"];
            $repetition = $trip["repetition"];
            $nb_place = $trip["place_max"];
            $txt_btn = LANG_TRIP["edit_this_trip"];
            $url_submit = $term->urlFor('web_edit_trip_post');
            $input_hidden = <<<END
<input value="{$trip["id_trajet"]}" type="text" name="id_trip" hidden/>
END;
        }
        $see_trips_of_same_itinerary = LANG_TRIP["see_trips_of_same_itinerary"];
        $hour_start_trip_str = LANG_TRIP["hour_start_trip"];
        $regularite = LANG_TRIP["regularite"];
        $first_date_trip = LANG_TRIP["first_date_trip"];
        $nb_places_str = LANG_TRIP["nb_places"];
        $driver_str = LANG_TRIP["driver"];
        $res .= <<<END
        <div class="notch_bar">
            <div class="notch_title">
                <div>$titleBold</div>
            </div>
            <div class="notch_right">
                <a href="{$term->urlFor('web_trips_get', ["id_itinerary" => $itineraire->id_itineraire])}"> 
                    <div class="btn_hoverable btn_in_notch d-flex justify-content-center align-items-center">
                        <img src="{$term->urlFor('img', ["routes" => "icons/enter.png"])}">
                    </div>
                </a>
                <div>{$see_trips_of_same_itinerary}</div>
            </div>
        </div>
    </div>


    <div class="no_scroll_page_content">
        <div class="container h-100">
            <form action="{$url_submit}" class="row h-100 py-2" data-method="post" 
            onsubmit="return submit_form(event,{},undefined,errorAlert);">
                <input value="{$itineraire->id_itineraire}" type="text" name="id_itinerary" hidden/>
                {$input_hidden}
                <div class="col-12 box_content_create_trip position-relative">

                    <div class="item_form_create_itinerary d-flex align-items-center my-3 mx-2 p-1">
                        <label class="text-center" for="time_start">
                            {$hour_start_trip_str}
                        </label>

                        <input class="form-control m-2" value="{$hour}" type="time" name="time_start" id="time_start" autocomplete="off"/>
                    </div>

                    <div class="item_form_create_itinerary d-flex align-items-center my-3 mx-2 p-1">
                        <label class="text-center" for="repetition">
                            {$regularite}
                        </label>
                        <select class="form-control m-2" name="repetition" id="repetition" required autocomplete="off">
END;

        $repetition_arr = [
            [Trajet::OUVRES_REPETITION, "Jours ouvrés"],
            [Trajet::WEEKEND_REPETITION, "Weekend"],
            [Trajet::UNIQUE_REPETITION, "Unique"],
            [Trajet::HEBDOMADAIRE_REPETITION, "Hebdomadaire"],
            [Trajet::MENSUEL_REPETITION, "Mensuel"],
            [Trajet::ANNUEL_REPETITION, "Annuel"],
        ];

        foreach ($repetition_arr as $r) {
            $selected = $repetition == $r[0] ? "selected" : "";
            $res .= <<<END
                                <option {$selected} value="{$r[0]}">{$r[1]}</option>
END;
        }
        $res .= <<<END
                        </select>
                    </div>

                    <div class="item_form_create_itinerary input_date_start d-none align-items-center my-3 mx-2 p-1">
                        <label class="text-center" for="date_start">
                            {$first_date_trip}
                        </label>
                        <input class="form-control m-2" value="{$date}" min="{$date_today}" type="date" name="date_start" id="date_start"/>
                    </div>

                    <div class="item_form_create_itinerary d-flex align-items-center my-3 mx-2 p-1">
                        <label class="text-center" for="nb_places">
                            {$nb_places_str}
                        </label>
                        <input class="form-control m-2" value="{$nb_place}" min="1" type="number" name="nb_places" id="nb_places" required/>
                    </div>

                    <div class="item_form_create_itinerary d-flex align-items-center my-3 mx-2 p-1">
                        <label class="text-center" for="driver">
                            {$driver_str}
                        </label>
                        <select class="form-control m-2" name="driver" id="driver" required>
END;

        foreach ($drivers as $d) {
            $selected = $d->email == $email ? "selected" : "";
            $res .= <<<END
                            <option {$selected} value="{$d->email}">{$d->email}</option>
END;
        }

        $hours_str = LANG_TRIP["hours"];
        $res .= <<<END
                        </select>
                    </div>

                    <div class="box_stops_create_itinerary d-flex justify-content-center align-items-center flex-wrap my-3 mx-2">

                        <div class="notch_hour_stops_box d-flex justify-content-center align-items-center mb-2">
                            <div class="notch_hour_stops text-center px-4 py-2 d-flex justify-content-center align-items-center">
                                <div
                                     onclick="post_button('{$term->urlFor('web_hours_create_trip_post')}',{id_itinerary:$('input[name=id_itinerary]').val(),time_start:$('input[name=time_start]').val()},refreshHourStops,errorAlert)"
                                     class="btn_hoverable btn_refresh_hours_stop d-flex justify-content-center align-items-center mr-3">
                                    <img src="{$term->urlFor('img', ["routes" => "icons/refresh.png"])}">
                                </div>
                                {$hours_str}
                            </div>
                        </div>
                        
                        <!-- Stops append here-->
END;

        if (!$is_creation) {
            $num = 1;
            foreach ($trip["hour_trip"] as $hour_trip) {
                $heure_passage = date("H:i", strtotime($hour_trip["heure_passage"]));
                $res .= <<<END
                <div class="stop_box d-flex flex-column justify-content-center align-items-center pt-3 px-2 pb-2 m-3 position-relative">
                    <div class="count_number position-absolute d-flex justify-content-center align-items-center p-1">
                        {$num}
                    </div>
                    <label class="title_box mb-2 text-center">
                        {$hour_trip["nom_arret"]}
                    </label>
                    <input class="form-control" type="time" name="hour_trip[]" value="{$heure_passage}" required>
                </div>
END;
                $num++;
            }
        }

        $res .= <<<END
                    </div>

                </div>

                <button class="btn_submit_create_trip px-5 py-4" type="submit">
                    {$txt_btn}
                </button>
            </form>
        </div>
    </div>
</div>
END;
        $res .= $gc->foot([$term->urlFor('js', ["routes" => "custom/create_edit_trip.js"])]);
        if ($is_creation) {
            $res .= <<<END
<script>
    $(".btn_refresh_hours_stop").click();
</script>
END;
        }

        return $res;
    }
}

