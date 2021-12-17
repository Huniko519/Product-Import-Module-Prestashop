<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__) . '/libraries/kahanit/Helpers.php');
require_once(dirname(__FILE__) . '/libraries/CategoryOption.php');
require_once(dirname(__FILE__) . '/libraries/ProductOption.php');

class PurechoiceImport extends Module
{
    public function __construct()
    {
        $this->name = 'purechoiceimport';
        $this->tab = 'quick_bulk_update';
        $this->version = '1.0.0';
        $this->author = 'Mykhailo Romaniuk';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Oshoot Import');
        $this->description = $this->l('This is oshoot products import module.');
        $this->confirmUninstall = $this->l('Are you sure?');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        // if ( !Db::getInstance()->execute( "DESCRIBE `my_table`" ) ) {
            
        //     Db::getInstance()->execute('
        //         DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'category_option`;
        //         CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'category_option` (
        //             `id_category_option` INT(10)       unsigned NOT NULL AUTO_INCREMENT,
        //             `id_category`        INT(10)       unsigned NOT NULL,
        //             `duty`               DECIMAL(12,4) unsigned NOT NULL,
        //             `shipping`           DECIMAL(12,4) unsigned NOT NULL,
        //             `width`              DECIMAL(12,4) unsigned NOT NULL,
        //             `height`             DECIMAL(12,4) unsigned NOT NULL,
        //             `depth`              DECIMAL(12,4) unsigned NOT NULL,
        //             `show_widget`        TINYINT(1)    unsigned NOT NULL,
        //             PRIMARY KEY (`id_category_option`)
        //         )
        //           ENGINE = ' . _MYSQL_ENGINE_ . '
        //           DEFAULT CHARSET = utf8;
        //     ');
        //     Db::getInstance()->execute('
        //         DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'product_option`;
        //         CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'product_option` (
        //             `id_product_option` INT(10)       unsigned NOT NULL AUTO_INCREMENT,
        //             `id_product`        INT(10)       unsigned NOT NULL,
        //             `upc`               VARCHAR(15)            NOT NULL,
        //             `caliber`           VARCHAR(15)            NOT NULL,
        //             `velocity`          VARCHAR(15)            NOT NULL,
        //             `image_url`         VARCHAR(765)           NOT NULL,
        //             `wnet`              DECIMAL(12,4) unsigned NOT NULL,
        //             `shipping`          DECIMAL(12,4) unsigned NOT NULL,
        //             `exchange`          DECIMAL(12,4) unsigned NOT NULL,
        //             `gst`               DECIMAL(12,4) unsigned NOT NULL,
        //             `duty`              DECIMAL(12,4) unsigned NOT NULL,
        //             `markup`            DECIMAL(12,4) unsigned NOT NULL,
        //             PRIMARY KEY (`id_product_option`)
        //         )
        //           ENGINE = ' . _MYSQL_ENGINE_ . '
        //           DEFAULT CHARSET = utf8;
        //     ');
        // }
        return parent::install() &&
               $this->registerHook('header') &&
               $this->registerHook('leftColumn') &&
               $this->registerHook('rightColumn') &&
               $this->registerHook('backOfficeHeader') &&
               $this->registerHook('displayBackOfficeHeader') &&
               $this->registerHook('displayAdminProductsExtra') &&
               $this->registerHook('actionProductUpdate') &&
               $this->registerHook('actionProductDelete') &&
               $this->registerHook('actionCategoryDelete') &&
               $this->registerHook('actionProductOutOfStock') &&
               $this->registerHook('displayProductTabContent') &&
               $this->registerHook('actionAdminProductsListingFieldsModifier') &&
               KIHelpers::installModuleTab('Gun Import', 'AdminPurechoiceImport', $this->name, 'AdminCatalog', true) &&
               KIHelpers::installModuleTab('Sales Report', 'AdminPurechoiceReport', $this->name, 'AdminCatalog', true);
    }

    public function uninstall()
    {
        // Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'category_option`;');
        // Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'product_option`;');
        // Configuration::deleteByName('PURECHOICE_OPTIONS');

        return parent::uninstall() &&
               $this->unregisterHook('header') &&
               $this->unregisterHook('leftColumn') &&
               $this->unregisterHook('rightColumn') &&
               $this->unregisterHook('backOfficeHeader') &&
               $this->unregisterHook('displayBackOfficeHeader') &&
               $this->unregisterHook('displayAdminProductsExtra') &&
               $this->unregisterHook('actionProductUpdate') &&
               $this->unregisterHook('actionProductDelete') &&
               $this->unregisterHook('actionCategoryDelete') &&
               $this->unregisterHook('actionProductOutOfStock') &&
               $this->unregisterHook('displayProductTabContent') &&
               $this->unregisterHook('actionAdminProductsListingFieldsModifier') &&
               KIHelpers::uninstallModuleTab('AdminPurechoiceImport') &&
               KIHelpers::uninstallModuleTab('AdminPurechoiceReport');
    }

    public function getContent()
    {
        $link = $this->context->link->getAdminLink('AdminPurechoiceImport');
        Tools::redirectAdmin($link);
        die();
    }

    public function hookBackOfficeHeader()
    {
    }

    public function hookHeader()
    {
    }

    public function hookLeftColumn()
    {
    }

    public function hookRightColumn()
    {
        $get_params = $_GET;

        $caliber = Tools::getValue('caliber', '');
        $velocity = Tools::getValue('velocity', '');
        $id_category = Tools::getValue('id_category', 0);

        $category_option = CategoryOption::getCategoryOptionByCategory($id_category);
        if ($category_option == false || !$category_option->show_widget) {
            return '';
        }

        unset($get_params['caliber']);
        unset($get_params['velocity']);
        unset($get_params['id_category']);
        unset($get_params['controller']);

        $options = json_decode(Configuration::get('PURECHOICE_OPTIONS'), true);
        $options = array_map('html_entity_decode', $options);

        $caliber_options = array_filter(explode("\n", $options['caliber_options']));
        $caliber_options = array_map(function ($caliber_option) {
            return explode(':', $caliber_option);
        }, $caliber_options);

        $velocity_options = array_filter(explode("\n", $options['velocity_options']));
        $velocity_options = array_map(function ($velocity_option) {
            return explode(':', $velocity_option);
        }, $velocity_options);

        $this->smarty->assign(array(
            'get_params'       => $get_params,
            'caliber'          => $caliber,
            'caliber_options'  => $caliber_options,
            'velocity'         => $velocity,
            'velocity_options' => $velocity_options
        ));

        return $this->display(__FILE__, 'views/templates/hook/filter_product.tpl');
        
    }

    public function hookDisplayBackOfficeHeader()
    {
    }

    public function hookDisplayAdminProductsExtra($params)
    {
        $id_product = (int)Tools::getValue('id_product');

        if (Validate::isLoadedObject($product = new Product($id_product))) {
            $product_option = ProductOption::getProductOptionByProduct($id_product);
            $this->context->smarty->assign([
                'pc_po_upc'      => $product_option->upc,
                'pc_po_caliber'  => $product_option->caliber,
                'pc_po_velocity' => $product_option->velocity,
                'token'          => Tools::getValue('token')
            ]);

            return $this->display(__FILE__, 'views/templates/admin/product_options_configure.tpl');
        }

        return '<div class="alert alert-warning">
            <button type="button" class="close" data-dismiss="alert">×</button>
            There is 1 warning.
            <ul style="display:block;" id="seeMore">
                <li>You must save this product before managing options.</li>
            </ul>
        </div>';
    }

    public function hookActionProductUpdate($params)
    {
        // check is page admin products
        if (Tools::getValue('controller', false) !== 'AdminProducts') {
            return;
        }

        // check product is posted and product is rental
        $pc_po_upc = Tools::getValue('pc_po_upc', '');
        $pc_po_caliber = Tools::getValue('pc_po_caliber', 0);
        $pc_po_velocity = Tools::getValue('pc_po_velocity', 0);
        $product = $params['product'];

        if ($product instanceof Product && ($pc_po_upc !== '' || $pc_po_caliber !== 0 || $pc_po_velocity !== 0)) {
            $product_option = ProductOption::getProductOptionByProduct($product->id);
            if ($product_option === false) {
                $product_option = new ProductOption();
            }

            $product_option->id_product = $product->id;
            $product_option->upc = $pc_po_upc;
            $product_option->caliber = $pc_po_caliber;
            $product_option->velocity = $pc_po_velocity;

            $product_option->save();
        }
    }

    public function hookActionProductDelete($params)
    {
        $product = $params['product'];

        if ($product instanceof Product) {
            $product_option = ProductOption::getProductOptionByProduct($product->id);

            if ($product_option !== false) {
                $product_option->delete();
            }
        }
    }

    public function hookActionCategoryDelete($params)
    {
        $category = $params['category'];

        if ($category instanceof Category) {
            $category_option = CategoryOption::getCategoryOptionByCategory($category->id);

            if ($category_option !== false) {
                $category_option->delete();
            }
        }
    }

    public function hookActionProductOutOfStock($params)
    {
        $product = $params['product'];

        $product_option = ProductOption::getProductOptionByProduct($product->id);
        $options = [];

        if ($product_option !== false) {
            if ($product_option->caliber != '') {
                $options['Caliber'] = $product_option->caliber;
            }
            if ($product_option->velocity != '') {
                $options['Velocity'] = $product_option->velocity;
            }
        }

        $this->context->smarty->assign([
            'options' => $options
        ]);
        
        return $this->display(__FILE__, 'views/templates/hook/product_options.tpl');
    }

    public function hookDisplayProductTabContent($params)
    {
    }

    public function hookActionAdminProductsListingFieldsModifier($fields)
    {
        $array = $fields['fields'];
        $key = 'active';
        $new = array(
            'caliber'  => array(
                'title'       => 'Caliber',
                'filter_type' => 'range',
                'align'       => 'text-right'
            ),
            'velocity' => array(
                'title'       => 'Velocity',
                'filter_type' => 'range',
                'align'       => 'text-right'
            )
        );
        $keys = array_keys($array);
        $index = array_search($key, $keys);
        $pos = false === $index ? count($array) : $index;
        $fields['fields'] = array_merge(array_slice($array, 0, $pos), $new, array_slice($array, $pos));

        if (isset($fields['select'])) {
            $fields['select'] .= ', caliber, velocity';
        }

        if (isset($fields['join'])) {
            $fields['join'] .= '
                LEFT JOIN `' . _DB_PREFIX_ . 'product_option` po ON po.`id_product` = a.`id_product`';
        }

        return $fields;
    }
}
