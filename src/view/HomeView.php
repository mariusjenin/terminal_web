<?php


namespace Terminal\View;


use Terminal\Controller\WebAppController\GlobalWebController;
use Terminal\TerminalApp;

class HomeView
{

    public function __construct()
    {
    }

    /**
     * Renvoie l'html de la page Home
     * @return string
     */
    public function renderHome()
    {
        $term = TerminalApp::getInstance();
        $gc = new GlobalWebController();
        $title = LANG_GLOBAL["home"];
        $res = $gc->head("Terminal - " . $title, [
            $term->urlFor('css', ["routes" => "custom/home.css"])
        ]);
        $res .= <<<END
<div class="content no_scroll_page_container">
    <div class="no_scroll_page_navbar">
END;
        $res .= $gc->navbar();

        $itineraries = LANG_ITINERARY["itineraries"];
        $create_itinerary = LANG_ITINERARY["create_itinerary"];
        $users = LANG_USER["users"];
        $create_account = LANG_USER["create_account"];
        $res .= <<<END
        <div class="notch_bar">
            <div class="notch_title">
                <div>$title</div>
            </div>
        </div>
    </div>


    <div class="no_scroll_page_content">
        <div class="d-flex flex-column justify-content-center align-items-center h-100">
            <div class="d-flex flex-row justify-content-center align-items-center w-100">
                <div class="w-50 btn_blue_home_container_left">
                    <a href="{$term->urlFor('web_itineraries_get')}">
                        <div class="m-4 p-1 btn_blue_home btn_hoverable">
                            <div class="img_btn_blue_home img_contains m-1">
                                <img src="{$term->urlFor('img', ["routes" => "icons/itinerary.png"])}">
                            </div>
                            <div class="mx-5">
                                {$itineraries}
                            </div>
                        </div>
                    </a>
                </div>
                <div class="w-50 btn_blue_home_container_right">
                    <a href="{$term->urlFor('web_create_itinerary_get')}">
                        <div class="m-4 p-1 btn_blue_home btn_hoverable">
                            <div class="mx-5">
                                {$create_itinerary}
                            </div>
                            <div class="img_btn_blue_home img_contains m-1">
                                <img src="{$term->urlFor('img', ["routes" => "icons/destination.png"])}">
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="d-flex flex-row justify-content-center align-items-center w-100">
                <div onclick="" class="w-50 btn_blue_home_container_left">
                    <a href="{$term->urlFor('web_users_get')}">
                        <div class="m-4 p-1 btn_blue_home btn_hoverable">
                            <div class="img_btn_blue_home img_fit m-1">
                                <img src="{$term->urlFor('img', ["routes" => "icons/man.png"])}">
                            </div>
                            <div class="mx-5">
                                {$users}
                            </div>
                        </div>
                    </a>
                </div>
                <div class="w-50 btn_blue_home_container_right">
                    <a href="{$term->urlFor('web_create_account_get')}">
                        <div class="m-4 p-1 btn_blue_home btn_hoverable position-relative">
                            <div class="mx-5">
                                {$create_account}
                            </div>
                            <div class="img_btn_blue_home m-1 img_fit">
                                <img src="{$term->urlFor('img', ["routes" => "icons/man.png"])}">
                            </div>
                            <div class="img_create_account">
                                <img src="{$term->urlFor('img', ["routes" => "icons/add.png"])}">
                            </div>
                        </div>
                    </a>
                </div>
            </div>
    
        </div>
    </div>
</div>
END;
        $res .= $gc->foot([]);
        return $res;
    }
}
