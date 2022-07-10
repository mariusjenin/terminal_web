<?php

namespace Terminal\View;

use Terminal\Manager\LanguageManager;
use Terminal\TerminalApp;

class GlobalView
{

    public function __construct()
    {
    }

    /**
     * Renvoie l'entete html du document (avec les CSS ici)
     * @param $titletab
     * @param $link array
     * @param array $link_extern
     * @return string
     */
    public function renderHead($titletab, array $link, array $link_extern)
    {
        $term = TerminalApp::getInstance();
        $res = <<<END
<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- Site Title -->
    <title>{$titletab}</title>
    <!-- Meta Character Set -->
    <meta charset="UTF-8">
    <!-- Icon -->
    <link rel="shortcut icon" href="{$term->urlFor('img', ["routes" => "identity/logo_terminal_app_round_bg.png"])}">
    <!-- CSS For Bootstrap -->
    <link href="{$term->urlFor('css', ["routes" => "bootstrap.min.css"])}" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link href="{$term->urlFor('css', ["routes" => "custom/color.css"])}" rel="stylesheet"/>
    <link href="{$term->urlFor('css', ["routes" => "custom/global.css"])}" rel="stylesheet"/>
    <link href="{$term->urlFor('css', ["routes" => "custom/modal.css"])}" rel="stylesheet"/>
END;
        foreach ($link_extern as $l) {
            $res .= <<<END
{$l}
END;
        }
        foreach ($link as $l) {
            $res .= <<<END
<link href="{$l}" rel="stylesheet"/>
END;
        }
        $res .= <<<END
</head>
<body>
END;
        return $res;
    }

    /**
     * Renvoie l'html de la navBar
     * @return string
     */
    public function renderNavbar()
    {
        $term = TerminalApp::getInstance();
        $menu = LANG_GLOBAL["menu"];
        $home = LANG_GLOBAL["home"];
        $itineraries = LANG_ITINERARY["itineraries"];
        $create_itinerary = LANG_ITINERARY["create_itinerary"];
        $users = LANG_USER["users"];
        $create_account = LANG_USER["create_account"];
        $logout = LANG_IDENTIFICATION["logout"];
        $res = <<<END
<nav class="navbar navbar-expand-sm navbar-dark d-flex justify-content-between align-items-center">

    <div class="mr-5 p-2 h-100 ">
        <a class="d-flex align-items-center h-100" href="{$term->urlFor('web_home_get')}">
            <div class="mw-100 h-100 p-1 navbar_bg_icon">
                <img class="mw-100 mh-100 " src="{$term->urlFor('img', ["routes" => "identity/logo_terminal_no_bg.png"])}">
            </div>
            <div class="font-weight-bold navbar-brand ml-4 mr-4 ">
                Terminal
            </div>
        </a>
    </div>

    <button class="navbar-toggler mr-4" type="button" data-toggle="collapse" data-target="#navbar1"
            aria-controls="navbar1" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="mx-4 my-2 collapse navbar-collapse justify-content-md-end" id="navbar1">
        <ul class="navbar-nav">
            <li class="nav-item dropdown align-self-lg-center ml-lg-3">
                <a class="dropdown-toggle nav-link" href="#" id="dropdown04" data-toggle="dropdown"
                   data-display="static" aria-haspopup="true" aria-expanded="false">
                    <span class="text-navbar">
                        {$menu}
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown04">
                    <a class="dropdown-item" href="{$term->urlFor('web_home_get')}">{$home}</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{$term->urlFor('web_itineraries_get')}">{$itineraries}</a>
                    <a class="dropdown-item" href="{$term->urlFor('web_create_itinerary_get')}">{$create_itinerary}</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{$term->urlFor('web_users_get')}">{$users}</a>
                    <a class="dropdown-item" href="{$term->urlFor('web_create_account_get')}">{$create_account}</a>
                    <div class="dropdown-divider"></div>
                    <a data-modal_accept="{$term->urlFor('web_logout_post')}" class="dropdown-item modal_logout" href="#">{$logout}</a>
                </div>
            </li>
        </ul>
    </div>
</nav>
END;
        return $res;
    }

    /**
     * Renvoie le bas de page (listes des script JS ici)
     * @param $script array
     * @return string
     */
    public function renderFoot(array $script, array $script_extern)
    {
        $term = TerminalApp::getInstance();
        $res = <<<END
<!-- JQuery-->
<script src="{$term->urlFor('js', ["routes" => "jquery-3.5.1.min.js"])}"></script>
<!-- JavaScript Popper -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<!-- JavaScript Bootstrap -->
<script src="{$term->urlFor('js', ["routes" => "bootstrap.min.js"])}"></script>
END;

        $lm = LanguageManager::getInstance();
        $lang = $lm->getLANG();
        $res .= <<<END
<!-- Custom JavaScript -->
<script src="{$term->urlFor('js', ["routes" => "lang/lang_" . $lang . ".js"])}"></script>
<script src="{$term->urlFor('js', ["routes" => "custom/submit_form.js"])}"></script>
<script src="{$term->urlFor('js', ["routes" => "custom/post_button.js"])}"></script>
<script src="{$term->urlFor('js', ["routes" => "custom/modal.js"])}"></script>
<script src="{$term->urlFor('js', ["routes" => "custom/navbar.js"])}"></script>
END;
        foreach ($script_extern as $scr) {
            $res .= <<<END
{$scr}
END;
        }
        foreach ($script as $scr) {
            $res .= <<<END
<script src="{$scr}"></script>
END;
        }
        $res .= <<<END
</body>
</html>
END;
        return $res;
    }
}

?>
