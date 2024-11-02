<?php

namespace Orto;

use Bitrix\Main\Page\Asset;

class Scripts
{
    public static function init()
    {
        global $APPLICATION;
        $currentPage = $APPLICATION->GetCurPage();

        if (strstr($currentPage, '/bitrix/')) {
            Asset::getInstance()->addJs('/bitrix/js/orto.ciblocksection/script.js');

            //для отладки и демо 
            // Asset::getInstance()->addJs('/local/modules/orto.ciblocksection/install/js/script.js');
        }
    }
}
