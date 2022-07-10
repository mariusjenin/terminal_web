<?php


namespace Terminal\View;


use Terminal\Controller\WebAppController\GlobalWebController;
use Terminal\Model\Utilisateur;
use Terminal\TerminalApp;

class UserView
{

    public function __construct()
    {
    }

    /**
     * Renvoie l'html de la page creér un compte
     * @return string
     */
    public function renderCreateAccount()
    {
        $term = TerminalApp::getInstance();
        $gc = new GlobalWebController();
        $title = LANG_USER["create_account_privilege"];
        $res = $gc->head("Terminal - " . $title, [
            $term->urlFor('css', ["routes" => "custom/create_driver_admin.css"])
        ]);
        $see_all_users = LANG_USER["see_all_users"];
        $error_value = LANG_DATA["error_value"];
        $driver_str = LANG_USER["driver"];
        $admin_str = LANG_USER["admin"];
        $driver_desc = LANG_USER["driver_desc"];
        $admin_desc = LANG_USER["admin_desc"];
        $email_account_to_create = LANG_USER["email_account_to_create"];
        $email = LANG_IDENTIFICATION["email"];
        $password = LANG_IDENTIFICATION["password"];
        $confirm = LANG_USER["confirm"];
        $create_this_account = LANG_USER["create_this_account"];
        $res .= <<<END
<div class="content no_scroll_page_container">
    <div class="no_scroll_page_navbar">
END;
        $res .= $gc->navbar();

        $administrator = Utilisateur::ADMINISTRATEUR_TYPE;
        $driver = Utilisateur::CONDUCTEUR_TYPE;
        $res .= <<<END
        <div class="notch_bar">
            <div class="notch_title">
                <div>$title</div>
            </div>
            <div class="notch_right">
                <a href="{$term->urlFor('web_users_get')}"> 
                    <div class="btn_hoverable btn_in_notch d-flex justify-content-center align-items-center">
                        <img src="{$term->urlFor('img', ["routes" => "icons/enter.png"])}">
                    </div>
                </a>
                <div>{$see_all_users}</div>
            </div>
        </div>
    </div>


    <div class="no_scroll_page_content">
        <div class="container h-100">
            <form action="" class="row h-100 py-2" data-method="post"
                  onsubmit="return submit_form(event);">
                <input value="2" type="text" name="id_itinerary" hidden/>
                <div class="col-12 box_content_create_driver_admin position-relative">
                            <div class="error_form d-none w-100 text-danger font-weight-bold justify-content-center align-items-center px-3 my-2">
                                {$error_value}
                            </div>

                    <div class="item_form_create_driver_admin d-flex my-4 p-3">
                        <div class="d-flex justify-content-around flex-column flex-md-row align-items-center w-100">
                            <div class="d-flex justify-content-center align-items-center">
                                <input class="mx-3" type="radio" value="{$driver}" name="type"
                                       id="driver" autocomplete="off" required/>
                                <label class="d-flex flex-column mx-3 w-100" for="driver">
                                    <span class="label_title">
                                        {$driver_str}
                                    </span>
                                    <span class="label_descr">
                                        {$driver_desc}
                                    </span>
                                </label>
                            </div>
                            <div class="d-flex justify-content-center align-items-center mt-3 mt-md-0">
                                <input class="mx-3" type="radio" value="{$administrator}" name="type"
                                       id="admin" autocomplete="off" required/>
                                <label class="d-flex flex-column mx-3 w-100" for="admin">
                                    <span class="label_title">
                                        {$admin_str}
                                    </span>
                                    <span class="label_descr">
                                        {$admin_desc}
                                    </span>
                                </label>
                            </div>


                        </div>
                    </div>

                    <div class="item_form_create_driver_admin d-flex flex-column my-4 p-2">
                        <label class="text-center" for="email">
                            {$email}
                        </label>

                        <input class="form-control m-2" placeholder="{$email_account_to_create}" type="text" name="email"
                               id="email" autocomplete="off" required/>
                    </div>

                    <div class="item_form_create_driver_admin d-flex flex-column my-4 p-2">
                        <label class="text-center" for="password">
                            {$password}
                        </label>

                        <input class="form-control m-2" placeholder="{$password}" type="password" name="password"
                               id="password" autocomplete="off" required/>
                        <input class="form-control m-2" placeholder="{$confirm}" type="password" name="password2"
                               id="password2" autocomplete="off" required/>
                    </div>


                </div>

                <button class="btn_submit_create_driver_admin px-5 py-4" type="submit">
                    {$create_this_account}
                </button>
            </form>
        </div>
    </div>
</div>
END;
        $res .= $gc->foot([]);
        return $res;
    }


    /**
     * Renvoie l'html de la page des utilisateurs
     * @return string
     */
    public function renderUsers($users)
    {
        $term = TerminalApp::getInstance();
        $gc = new GlobalWebController();
        $title = LANG_USER["users_list"];
        $res = $gc->head("Terminal - " . $title, [
            $term->urlFor('css', ["routes" => "custom/users.css"])
        ]);
        $create_user = LANG_USER["create_account"];
        $filtrate_result = LANG_USER["filtrate_result"];
        $no_user_with_email = LANG_USER["no_user_with_email"];
        $new_password = LANG_USER["new_password"];
        $confirm = LANG_USER["confirm"];

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
                <a href="{$term->urlFor('web_create_account_get')}"> 
                    <div class="btn_hoverable btn_in_notch d-flex justify-content-center align-items-center">
                        <img src="{$term->urlFor('img', ["routes" => "icons/add.png"])}">
                    </div>
                </a>
                <div>{$create_user}</div>
            </div>
        </div>
    </div>


    <div class="scroll_page_content">
        <div class="container mt-3">
            <div class="row p-1 p-sm-0 justify-content-center flex-column align-items-center">
                <div class="box_email_user_like d-flex flex-column justify-content-center align-items-center">
                    <label for="email_user_like">{$filtrate_result}</label>
                    <input class="form-control" type="text" id="email_user_like" name="email_user_like">
                </div>
                <div class="box_user_list w-100 p-2 mt-4 mb-3">

                    <div class="text-center m-4 no_email_user d-none">
                        {$no_user_with_email}
                    </div>
END;
        foreach ($users as $u) {

            $res .= <<<END


                    <form class="line_user d-flex flex-row justify-content-center m-4">
                        <div class="line_user_content d-flex flex-column flex-lg-row justify-content-around align-items-center px-3 py-2">
                            <div class="px-2 py-1 py-lg-0 text-left">{$u->email}</div>
                            <div class="px-2 py-1 py-lg-0 ">
                                <input class="form-control" type="password" name="password"
                                       placeholder="{$new_password}">
                            </div>
                            <div class="px-2 py-1 py-lg-0">
                                <input class="form-control" type="password" name="password2"
                                       placeholder="{$confirm}">
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-center">
                            <div data-modal_accept="{$term->urlFor('web_edit_password_post')}" 
                            data-modal_accept_data='{ "id_user": {$u->id_utilisateur}}'
                            class="btn_user btn_submit deactivated">
                                <img src="{$term->urlFor('img', ["routes" => "icons/check.png"])}">
                            </div>
                            <div data-modal_accept="{$term->urlFor('web_delete_user_post')}" 
                            data-modal_accept_data='{ "id_user": {$u->id_utilisateur}}'
                            class="btn_user btn_delete">
                                <img src="{$term->urlFor('img', ["routes" => "icons/bin.png"])}">
                            </div>
                        </div>
                    </form>
END;

        }

        $res .= <<<END
                </div>
            </div>
        </div>
    </div>
</div>
END;
        $res .= $gc->foot([$term->urlFor('js', ["routes" => "custom/users.js"])]);
        return $res;
    }
}
