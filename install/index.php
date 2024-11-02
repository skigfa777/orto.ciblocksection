<?php

use Bitrix\Main\ModuleManager;

class orto_CIBlockSection extends CModule
{
    public $MODULE_ID = 'orto.ciblocksection';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $PARTNER_URI;
    public $MODULE_GROUP_RIGHTS;

    public function __construct()
    {
        include __DIR__ . '/version.php';

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->MODULE_NAME = 'Копирование разделов ИБ';
        $this->MODULE_DESCRIPTION = 'Модуль добавляет функцию копирования раздела с вложенными элементами в контекстное меню списка разделов ИБ';
        $this->PARTNER_NAME = 'Алексей Бабушкин';
        $this->PARTNER_URI = '';
        $this->MODULE_GROUP_RIGHTS = 'N';
    } 

    function InstallFiles()
    {
        // copy admin files
        if (!CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/local/modules/" . $this->MODULE_ID . "/install/admin", 
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin/"  . $this->MODULE_ID, $ReWrite = true, $Recursive = true
        )) {
            throw new Exception('Ошибка! Не получилось создать папку в bitrix/admin');
        }

        // copy js files
        if (!CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/local/modules/" . $this->MODULE_ID . "/install/js",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/js/" . $this->MODULE_ID, $ReWrite = true, $Recursive = true
        )) {
            throw new Exception('Ошибка! Не получилось создать папку в bitrix/js');
        }

        return true;
    }

    public function UnInstallFiles()
    {        
        // delete admin files
        if(is_dir($_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/".$this->MODULE_ID)) {                    
            DeleteDirFilesEx("/bitrix/admin/".$this->MODULE_ID);
        }  

        // delete js files
        if(is_dir($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".$this->MODULE_ID)) {                    
            DeleteDirFilesEx("/bitrix/js/".$this->MODULE_ID);
        }
        
        return true;
    } 

    public function DoInstall()
    {
        $this->InstallFiles();
        $this->InstallEvents();
        ModuleManager::registerModule($this->MODULE_ID);
    }
    
    public function DoUninstall()
    {
        $this->UnInstallFiles();
        $this->UnInstallEvents();
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    public function InstallEvents()
    {
        RegisterModuleDependences("main", "OnEpilog", "orto.CIBlockSection", "Orto\Scripts", "init", "100");
        // RegisterModuleDependences("main", "onAdminListDisplay", "orto.CIBlockSection", "Orto\Context", "onAdminCustomListDisplay", "100");
        return true;
    }

    public function UnInstallEvents()
    {
        UnRegisterModuleDependences("main", "OnEpilog", "orto.CIBlockSection", "Orto\Scripts", "init", "100");
        // UnRegisterModuleDependences("main", "OnAdminListDisplay", "orto.CIBlockSection", "Orto\Context", "onAdminCustomListDisplay", "100");
        return false;
    }
}
