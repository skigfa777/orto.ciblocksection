<?php
namespace Orto;

class Context
{
    public static int $IBLOCK_ID = 17;
    public static string $IBLOCK_TYPE = 'company_steplife';

    public static function onAdminCustomListDisplay(&$list)
    {
        global $APPLICATION;
        $curUri = new \Bitrix\Main\Web\Uri($APPLICATION->GetCurUri());
        parse_str($curUri->getQuery(), $params);

        // echo '<pre>' . print_r($list,1) . '</pre>';

        $type = self::$IBLOCK_TYPE;
        $iblock = self::$IBLOCK_ID;

        // Добавить пункт в контекстное меню
        if ( $list->table_id == "tbl_iblock_list_".md5($type.".".$iblock) ) {
            foreach ($list->aRows as $row) {
                $ids = array_column($row->aActions, 'ID');
                if (in_array('edit_section', $ids)) {
                    $row->aActions["copy_section"]["TEXT"] = "Копировать";
                    $row->aActions["copy_section"]["ACTION"] = "javascript:copy_section(".substr($row->id, 1).")";
                }
            }
        }
    }
}
