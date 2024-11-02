<?php

namespace Orto;

$arClasses = array(
    'Orto\Scripts' => 'lib/scripts.php',
    'Orto\Context' => 'lib/context.php',
);

\Bitrix\Main\Loader::registerAutoLoadClasses("orto.ciblocksection", $arClasses);

use Bitrix\Main\SystemException;

class CIBlockSection 
{
    public int $IBLOCK_ID;
    public int $SECTION_ID;
    public string $SECTION_CODE;
    public string $LAST_ERROR;
    public string $LAST_MESSAGE;

    function __construct()
    {
        if (!\CModule::IncludeModule("iblock"))
            throw new SystemException("Отсутствует модуль iblock!");
    }

    private function getElementsID() 
    {
        $arSelect = array("ID", "NAME", "DATE_ACTIVE_FROM");
        $arFilter = array(
            "IBLOCK_ID" => $this->IBLOCK_ID,
            "SECTION_ID" => $this->SECTION_ID,
        );

        $res = \CIBlockElement::GetList(
            array("SORT" => "ASC"),
            $arFilter,
            $arGroupBy=false,
            $arNavStartParams=false,
            $arSelect
        );

        $arElements = array();

        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $arElements[] = array(
                "ID" => $arFields["ID"],
                "NAME" => $arFields["NAME"],
            );
        }

        return $arElements;
    }

    private function copySection() 
    {
        $bs = new \CIBlockSection();
        $res = $bs->GetByID($this->SECTION_ID);

        $arRes = $res->GetNext();

        // echo '<pre>' . print_r($arRes,1) . '</pre>';

        if (!$arRes)
            return "Раздела с ID=" . $this->SECTION_ID . " не существует!";

        $milliseconds = floor(microtime(true) * 1000);
        $name = $arRes["NAME"] . " (копия)";

        $sectionPageUrl = $bs->generateMnemonicCode($name . md5($milliseconds), $this->IBLOCK_ID);

        $arFields = array(
            "ACTIVE" => "Y",
            "IBLOCK_SECTION_ID" => $arRes["IBLOCK_SECTION_ID"],
            "IBLOCK_ID" => $arRes["IBLOCK_ID"],
            "NAME" => $name,
            "CODE" => $sectionPageUrl,
            "SORT" => $arRes["SORT"],
        );

        $ID = $bs->Add($arFields);

        if (!$ID) {
            $this->LAST_ERROR = $bs->LAST_ERROR;
        }    
        $this->SECTION_CODE = $sectionPageUrl;
        return $ID;
    }

    public function copy() 
    {
        $sectionId = $this->copySection();

        if (!$sectionId) {
            $this->LAST_ERROR = "Не удалось создать копию раздела #{$this->SECTION_ID}!<br>{$this->LAST_ERROR}";
            return false;
        }

        $arElements = $this->getElementsID();

        //после того как получили элементы из текущего раздела, меняем значение свойства на ID раздела, который создали
        $this->SECTION_ID = $sectionId;

        $this->LAST_MESSAGE = "<b>Создан новый раздел <a href=\"/{$this->SECTION_CODE}\" target=\"_blank\">#{$this->SECTION_ID}</a>.</b>";
        foreach($arElements as $element) {
            $elementId = $this->copyElement($element["ID"]);
            if (is_int($elementId))
                $this->LAST_MESSAGE .= "<br> - элемент #{$element["ID"]} ({$element["NAME"]}) скопирован";
            else {
                return false;
            }
        }

        return true;
    }

    public function copyElement(int $ELEMENT_ID) 
    {
        // Получаем данные элемента
        $element = \CIBlockElement::GetByID($ELEMENT_ID)->GetNextElement();

        if (!$element) {
            $this->LAST_ERROR = "Элемент с ID " . $ELEMENT_ID . " не найден.";
            return false;
        }

        $fields = $element->GetFields();
        $properties = $element->GetProperties();

        // echo '<pre>' . print_r($properties,1) . '</pre>';

        // Подготавливаем данные для нового элемента
        $newElementFields = array(
            "IBLOCK_ID" => $this->IBLOCK_ID,
            "IBLOCK_SECTION_ID" => $this->SECTION_ID,
            "NAME" => $fields["NAME"] . " (копия)",
            "ACTIVE" => $fields["ACTIVE"],
            "DATE_ACTIVE_FROM" => $fields["DATE_ACTIVE_FROM"],
            "SORT" => $fields["SORT"],
            "DETAIL_TEXT" => $fields["DETAIL_TEXT"],
            "PREVIEW_TEXT" => $fields["PREVIEW_TEXT"],
            "DETAIL_PICTURE" => \CFile::MakeFileArray($fields["DETAIL_PICTURE"]),
            "PREVIEW_PICTURE" => \CFile::MakeFileArray($fields["PREVIEW_PICTURE"]),
        );

        // Добавляем свойства
        $newElementProperties = array();

        foreach ($properties as $propCode => $propValue) {
            switch ($propValue['PROPERTY_TYPE']) {
                case 'L'://список
                    $newElementProperties[$propCode] = $propValue['VALUE_ENUM_ID'];
                    break;

                case 'F'://файл
                    if ($propValue['MULTIPLE']=='Y') {
                        if (is_array($propValue['VALUE'])) {
                            foreach ($propValue['VALUE'] as $key => $arElEnum) {
                                $newElementProperties[$propCode][] = \CFile::MakeFileArray(\CFile::CopyFile($arElEnum));
                            }
                        }                 
                    } else {
                        $newElementProperties[$propCode] = \CFile::MakeFileArray(\CFile::CopyFile($propValue['VALUE']));
                    }
                    break;

                default:
                    /*
                    S - строка,
                    G - связанный список
                    */
                    //bitrix-эквивалент htmlspecialchars_decode, htmlspecialchars_decode тут не сработает
                    $newElementProperties[$propCode] = htmlspecialcharsBack($propValue["VALUE"]);

            }
        }

        // Создаем новый элемент
        $newElement = new \CIBlockElement;
        $newElementId = $newElement->Add($newElementFields);

        if ($newElementId) {
            // Устанавливаем свойства нового элемента
            \CIBlockElement::SetPropertyValuesEx($newElementId, $iblockId, $newElementProperties);  
        } else {
            $this->LAST_ERROR = "Ошибка при копировании элемента: " . $newElement->LAST_ERROR;
        }
        return $newElementId;
    }
}
