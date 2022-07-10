<?php

namespace Terminal\View;

use Terminal\Controller\WebAppController\GlobalWebController;
use Terminal\TerminalApp;

class IdentificationView
{

    public function __construct()
    {
    }

    /**
     * Renvoie l'html de la page de connexion
     * @return string
     */
    public function renderLogin()
    {
        $term = TerminalApp::getInstance();
        $gc = new GlobalWebController();
        $title = LANG_IDENTIFICATION["sign_in"];
        $error_values = LANG_DATA["error_value"];
        $res = $gc->head('Terminal - ' . $title, [$term->urlFor('css', ["routes" => "custom/login.css"])]);
        $email = LANG_IDENTIFICATION["email"];
        $your_email = LANG_IDENTIFICATION["your_email"];
        $password = LANG_IDENTIFICATION["password"];
        $your_password = LANG_IDENTIFICATION["your_password"];
        $res .= <<<END
<div class="brand_login">
    <img src="{$term->urlFor('img', ["routes" => "identity/logo_terminal_no_bg.png"])}">
</div>
<div class="background_gradient_animated">
    <div class="container-fluid d-flex justify-content-center align-items-center flex-column">
        <div class="d-flex vh-100 justify-content-between align-items-center">
            <div class="login_block">
                <div class="h1 px-3 py-2 login_title">
                    {$title}
                </div>
                <form action="{$term->urlFor('web_login_post')}" data-method="post" class="login_form"
                      onsubmit="return submit_form(event);">
                    <div class="row">
                        <div class="col-12">
                            <div class="error_form d-none w-100 text-danger font-weight-bold justify-content-center align-items-center pl-3 pr-3 mb-2">
                                {$error_values}
                            </div>
                            <div class="item_field_login">
                                <label for="email" class="d-block font-weight-bold">{$email}</label>
                                <input id="email" name="email" class="form-control d-block" type="text" placeholder="{$your_email}"
                                       required>
                            </div>
                            <div class="item_field_login">
                                <label for="password" class="d-block font-weight-bold">{$password}</label>
                                <input id="password" class="form-control d-block" type="password"
                                       placeholder="{$your_password}"
                                       name="password"
                                       required>
                            </div>
                        </div>

                        <div class="block_submit w-100 d-flex align-items-center flex-column text-center">
                            <button class="submit_login btn_hoverable" type="submit">
                                <img class="mh-100 mw-100" src="{$term->urlFor('img', ["routes" => "icons/check.png"])}">
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
END;
        $res .= $gc->foot([]);
        return $res;
    }
}


?>
