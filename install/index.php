<?php

use Bitrix\Main\EventManager;
use Bitrix\Main\ModuleManager;

class frizus_reviews extends CModule
{
    public function __construct()
    {
        $arModuleVersion = null;
        include __DIR__ . '/version.php';
        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }
        $this->MODULE_ID = 'frizus.reviews';
        $this->MODULE_NAME = 'Контроллер с отзывами';
        $this->MODULE_DESCRIPTION = "Для работы требуется модуль https://github.com/andreyryabin/sprint.migration\nПосле установки требуется установить миграции в Настройки -> Миграции для разработчиков -> Миграции (cfg)";
        $this->MODULE_GROUP_RIGHTS = 'N';
        $this->PARTNER_NAME = 'frizus';
        $this->PARTNER_URI = '';
    }

    public function doInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandlerCompatible('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, "\\Frizus\\Reviews\\UserProperty\\IBlockAndSection", 'GetPropertyDescription');
        CopyDirFiles(__DIR__ . '/css/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/css/' . $this->MODULE_ID . '/', true, true);
        CopyDirFiles(__DIR__ . '/js/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/js/' . $this->MODULE_ID . '/', true, true);
        CopyDirFiles(__DIR__ . '/tools/' . $this->MODULE_ID . '/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/tools/' . $this->MODULE_ID . '/', true, true);
        CopyDirFiles(__DIR__ . '/migrations/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface/migrations/', true, true);
    }

    public function doUninstall()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, "\\Frizus\\Reviews\\UserProperty\\IBlockAndSection", 'GetPropertyDescription');
        DeleteDirFiles(__DIR__ . '/css/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/css/' . $this->MODULE_ID . '/');
        DeleteDirFiles(__DIR__ . '/js/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/js/' . $this->MODULE_ID . '/');
        DeleteDirFiles(__DIR__ . '/tools/' . $this->MODULE_ID . '/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/tools/' . $this->MODULE_ID . '/');
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }
}