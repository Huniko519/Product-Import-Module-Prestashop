<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_0_2()
{
    $options = unserialize(Configuration::get('PURECHOICE_OPTIONS'));
    Configuration::updateValue('PURECHOICE_OPTIONS', json_encode($options));

    return true;
}
