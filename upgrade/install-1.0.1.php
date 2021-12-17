<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_0_1()
{
    Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'category_option`
        ADD COLUMN `show_widget` TINYINT(1) UNSIGNED NOT NULL;
    ');

    return true;
}
