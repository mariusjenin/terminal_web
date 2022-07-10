<?php


namespace Terminal\View;


use Terminal\Controller\WebAppController\GlobalWebController;
use Terminal\Model\Trajet;
use Terminal\Model\Utilisateur;
use Terminal\TerminalApp;

class ItineraryView
{

    public function __construct()
    {
    }

    /**
     * Renvoie l'html de la page Itineraires
     * @return string
     */
    public function renderItineraries(array $itineraries)
    {
        $term = TerminalApp::getInstance();
        $gc = new GlobalWebController();
        $title = LANG_ITINERARY["itineraries"];
        $create_itinerary = LANG_ITINERARY["itineraries"];
        $res = $gc->head("Terminal - " . $title, [
            $term->urlFor('css', ["routes" => "custom/itineraries.css"])
        ]);
        $res .= <<<END
<div class="content scroll_page_container">
    <div class="scroll_page_navbar">
END;
        $res .= $gc->navbar();
        $res .= <<<END
        <div class="notch_bar">
            <div class="notch_title">
                <div>$title</div>
            </div>
            <div class="notch_right">
                <a href="{$term->urlFor('web_create_itinerary_get')}"> 
                    <div class="btn_hoverable btn_in_notch d-flex justify-content-center align-items-center">
                        <img src="{$term->urlFor('img', ["routes" => "icons/add.png"])}">
                    </div>
                </a>
                <div>{$create_itinerary}</div>
            </div>
        </div>
    </div>


    <div class="scroll_page_content">
        <div class="container mt-3">
            <div class="row p-1 p-sm-0">
END;


        foreach ($itineraries as $it) {
            $trips = $it["trips"];

            $has_trips = count($trips) > 0;

            $res .= <<<END
                <div class="itinerary_box p-2 my-2 col-12">
                    <div class="line_itinerary d-flex flex-row align-items-center justify-content-center">
                        <a href="{$term->urlFor('web_trips_get', ["id_itinerary" => $it["id_itinerary"]])}"
                         class="title_itinerary d-flex flex-row align-items-center px-3">
                                {$it["name_itinerary"]}
                        </a>
                        <a href="{$term->urlFor('web_trips_get', ["id_itinerary" => $it["id_itinerary"]])}">
                            <div class="btn_itinerary btn_trips_itinerary">
                                <img src="{$term->urlFor('img', ["routes" => "icons/planning.png"])}">
                            </div>
                        </a>
                        <div data-modal_accept="{$term->urlFor('web_delete_itinerary_post')}" 
                        data-modal_accept_data='{ "id_itinerary": {$it["id_itinerary"]}}'
                        class="btn_itinerary btn_delete_itinerary">
                            <img src="{$term->urlFor('img', ["routes" => "icons/bin.png"])}">
                        </div>
                        <div class="btn_itinerary btn_switch_planning_itinerary">
                            <img src="{$term->urlFor('img', ["routes" => "icons/triangle.png"])}">
                        </div>
                    </div>
END;

            $array_stops = [];
            $array_weekend = [];
            $array_ouvres = [];
            $array_trip_to_date = [];
            $line_head_stops = LANG_ITINERARY["stops"];
            $line_head_weekend = 0;
            $line_head_ouvres = 0;
            $line_head_trip_to_date = [];
            for ($i = 0; $i < count($it["stops"]); $i++) {
                $array_stops[] = $it["stops"][$i]["nom_arret"];
            }


            $no_trips = LANG_ITINERARY["no_trips"];
            $from_m_to_f = LANG_ITINERARY["from_m_to_f"];
            $weekend = LANG_ITINERARY["weekend"];

            for ($i = 0; $i < count($trips); $i++) {

                $hours = $trips[$i]["time_passage"];
                switch ($trips[$i]["repetition"]) {
                    case Trajet::UNIQUE_REPETITION:
                    case Trajet::HEBDOMADAIRE_REPETITION:
                    case Trajet::MENSUEL_REPETITION:
                    case Trajet::ANNUEL_REPETITION:
                        if (isset($line_head_trip_to_date[$trips[$i]["date_depart"]])) {
                            $line_head_trip_to_date[$trips[$i]["date_depart"]]++;
                        } else {
                            $line_head_trip_to_date[$trips[$i]["date_depart"]] = 1;
                        }
                        for ($j = 0; $j < count($hours); $j++) {
                            $array_trip_to_date[$j][] = $hours[$j]["time"];
                        }
                        break;
                    case Trajet::OUVRES_REPETITION:
                        $line_head_ouvres++;
                        for ($j = 0; $j < count($hours); $j++) {
                            $array_ouvres[$j][] = $hours[$j]["time"];
                        }
                        break;
                    case Trajet::WEEKEND_REPETITION:
                        $line_head_weekend++;
                        for ($j = 0; $j < count($hours); $j++) {
                            $array_weekend[$j][] = $hours[$j]["time"];
                        }
                        break;
                }
            }

            if (!$has_trips) {
                $res .= <<<END
                    <div class="w-100 mt-2 p-2" style="display: none">
                        <div class="table_itineraries text-center no_trips_in_itinerary">
                            {$no_trips}
                        </div>
                    </div>
END;
            } else {
                $res .= <<<END
                    <div class="w-100 mt-2 p-2" style="display: none">
                        <table class="table_itineraries w-100">
                            <tr class="table_itineraries_head">
END;

                $res .= <<<END
                                <th class="last_of_section">$line_head_stops</th>
END;
                if ($line_head_ouvres > 0) {
                    $res .= <<<END
                                <th class="last_of_section" colspan="{$line_head_ouvres}">{$from_m_to_f}</th>
END;
                }
                if ($line_head_weekend > 0) {
                    $res .= <<<END
                                <th class="last_of_section" colspan="{$line_head_weekend}">{$weekend}</th>
END;
                }
                foreach ($line_head_trip_to_date as $str => $nb) {
                    $res .= <<<END
                                <th colspan="$nb" class="last_of_section">$str</th>
END;
                }
                $res .= <<<END
                            </tr>
END;

                for ($i = 0; $i < count($array_stops); $i++) {
                    $res .= <<<END
                            <tr class="table_itineraries_row">
END;
                    $res .= <<<END
                                <td class="last_of_section">{$array_stops[$i]}</td>
END;

                    if (isset($array_ouvres[$i])) {
                        for ($j = 0; $j < count($array_ouvres[$i]); $j++) {
                            $classe = $j + 1 == $line_head_ouvres ? 'class="last_of_section"' : "";
                            $res .= <<<END
                                <td $classe>{$array_ouvres[$i][$j]}</td>
END;
                        }
                    }
                    if (isset($array_weekend[$i])) {
                        for ($j = 0; $j < count($array_weekend[$i]); $j++) {
                            $classe = $j + 1 == $line_head_weekend ? 'class="last_of_section"' : "";
                            $res .= <<<END
                                <td $classe>{$array_weekend[$i][$j]}</td>
END;
                        }
                    }
                    if (isset($array_trip_to_date[$i])) {
                        foreach ($array_trip_to_date[$i] as $str) {
                            $res .= <<<END
                                <td>$str</td>
END;
                        }
                    }

                    $res .= <<<END
                            </tr>
END;
                }
                $res .= <<<END
                        </table>
                    </div>
END;
            }
            $res .= <<<END
                    </div>
END;
        }

        $res .= <<<END
            </div>
        </div>
    </div>
</div>
END;
        $res .= $gc->foot([$term->urlFor('js', ["routes" => "custom/itinerary.js"])]);
        return $res;
    }

    /**
     * Renvoie l'html de la page Créer un itinéraire
     */
    public function renderCreateItinerary()
    {
        $term = TerminalApp::getInstance();
        $gc = new GlobalWebController();
        $title = LANG_ITINERARY["create_itinerary"];
        $res = $gc->head("Terminal - " . $title, [
                $term->urlFor('css', ["routes" => "custom/create_itinerary.css"])
            ]
            , [<<<END
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
          integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A=="
          crossorigin=""/>
END
            ]);
        $res .= <<<END
<div class="content scroll_page_container no_scroll_page_container-lg">
    <div class="scroll_page_navbar no_scroll_page_navbar-lg">
END;
        $res .= $gc->navbar();

        $see_all_itineraries = LANG_TRIP["see_all_itineraries"];
        $path = LANG_ITINERARY["path"];
        $center = LANG_ITINERARY["center"];
        $name_itinerary = LANG_ITINERARY["name_itinerary"];
        $name_itinerary_desc = LANG_ITINERARY["name_itinerary_desc"];
        $click_to_display = LANG_ITINERARY["click_to_display"];
        $see_itinerary_path_detailed = LANG_ITINERARY["see_itinerary_path_detailed"];
        $error_during_itinerary_creation = LANG_ITINERARY["error_during_itinerary_creation"];
        $create_this_itinerary = LANG_ITINERARY["create_this_itinerary"];

        $res .= <<<END
        <div class="notch_bar">
            <div class="notch_title">
                <div>$title</div>
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


    <div class="scroll_page_content no_scroll_page_content-lg">
        <div class="container-fluid h-100">
            <form action="{$term->urlFor('web_create_itinerary_post')}" data-method="post"
                  onsubmit="return submit_create_itinerary(event);" class="row h-100">

                <div class="col-12 col-lg-6 d-flex position-relative">

                    <div class="switch_group_create_itinerary d-flex justify-content-center align-items-center">
                        <div class="switch_see_itinerary p-2 d-flex justify-content-center align-items-center flex-column">
                            <div class="pb-2">
                                {$path}
                            </div>
                            <label class="switch">
                                <input checked oninput="drawItinerary()" type="checkbox">
                                <span class="slider round"></span>
                            </label>
                        </div>
                        <div class="switch_fit_bounds p-2 d-flex justify-content-center align-items-center flex-column">
                            <div class="pb-2">
                                {$center}
                            </div>
                            <label class="switch">
                                <input checked oninput="centerAroundBounds()" type="checkbox">
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                    <div onmouseleave="closeAllPopup()" class="flex-grow-1 flex-shrink-1 my-3 mr-0 mr-lg-3"
                         id="map_create_itinerary"></div>
                </div>

                <div class="col-12 col-lg-6 d-flex flex-column left_column_create_itinerary">
                    <div class="itinerary_edit_name d-flex justify-content-center align-items-center flex-column">
                        <label for="name_itinerary"><b>{$name_itinerary}</b> {$name_itinerary_desc}</label>
                        <input id="name_itinerary" class="form-control" type="text"
                               placeholder="{$name_itinerary}"
                               name="name_itinerary"
                               required>
                    </div>
                    <div class="itinerary_edit_box position-relative pb-3">
                        <div class="itinerary_edit_box_list h-100 d-flex flex-row flex-wrap justify-content-center align-items-center pt-5 pb-2 py-2">
                            <div class="notch_stops_itinerary px-4 py-2">
                                {$click_to_display}
                            </div>

                            <!-- List of stops-->

                        </div>
                    </div>

                    <div class="submit_box">
                        <div class="error_form w-100 text-danger d-none font-weight-bold justify-content-center align-items-center pl-3 pr-3 mt-2">
                            {$error_during_itinerary_creation}
                        </div>
                        <div class="d-flex justify-content-around align-items-center flex-row">
                            <div onclick="drawItineraryDetailed(' {$term->urlFor('web_itinerary_json_post')} ')"
                                 class="btn_hoverable btn_display_itinerary deactivated px-4 py-3 m-2 text-center">
                                {$see_itinerary_path_detailed}
                            </div>

                            <input class="btn_hoverable btn_create_itinerary px-5 py-4 m-2 btn font-weight-bold"
                                   type="submit" value="{$create_this_itinerary}">
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
END;

        $res .= $gc->foot([
            $term->urlFor('js', ["routes" => "config/config.js"]),
            $term->urlFor('js', ["routes" => "custom/create_itinerary.js"])],
            [
                <<<END
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"
        integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA=="
        crossorigin=""></script>
END
            ]);
        return $res;
    }
}

