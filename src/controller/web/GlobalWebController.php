<?php

namespace Terminal\Controller\WebAppController;


use Terminal\View\GlobalView;

class GlobalWebController
{
    public static function navbar()
    {
        $gv = new GlobalView();
        return $gv->renderNavbar();
    }

    public static function head($titletab, array $link, array $link_extern = [])
    {
        $gv = new GlobalView();
        return $gv->renderHead($titletab, $link, $link_extern);
    }

    public static function foot(array $script, array $script_extern = [])
    {
        $gv = new GlobalView();
        return $gv->renderFoot($script, $script_extern);
    }
}

?>
