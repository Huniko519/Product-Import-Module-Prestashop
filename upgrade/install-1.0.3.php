<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_0_3()
{
    Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'category_option`
        ADD COLUMN `width` DECIMAL(12,4) NOT NULL DEFAULT \'-1\' AFTER `shipping`,
        ADD COLUMN `height` DECIMAL(12,4) NOT NULL DEFAULT \'-1\' AFTER `width`,
        ADD COLUMN `depth` DECIMAL(12,4) NOT NULL DEFAULT \'-1\' AFTER `height`;;
    ');

    return true;
}
