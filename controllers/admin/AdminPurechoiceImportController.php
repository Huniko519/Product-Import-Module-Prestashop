<?php

@ini_set('max_execution_time', 0);

/** correct Mac error on eof */
@ini_set('auto_detect_line_endings', '1');

/** No max line limit since the lines can be more than 4096. Performance impact is not significant. */
define('MAX_LINE_SIZE', 0);

/** Used for validatefields diying without user friendly error or not */
define('UNFRIENDLY_ERROR', false);

/** this value set the number of columns visible on each page */
define('MAX_COLUMNS', 6);

require_once dirname(__FILE__) . '/../../libraries/CategoryOption.php';
require_once dirname(__FILE__) . '/../../libraries/ProductOption.php';
require_once dirname(__FILE__) . '/../../libraries/PIUploadHandler.php';
require_once dirname(__FILE__) . '/../../libraries/kahanit/Products.php';

class AdminPurechoiceImportController extends ModuleAdminController
{
    private $statistics = [
        'total-rows'               => 0,
        'rows-processed'           => 0,
        'products-created'         => 0,
        'products-updated'         => 0,
        'products-excluded'        => 0,
        'products-failed'          => 0,
        'categories-created'       => 0,
        'categories-failed'        => 0,
        'manufacturers-created'    => 0,
        'products-created-data'    => [],
        'products-excluded-skus'   => [],
        'products-failed-skus'     => [],
        'products-failed-messages' => [],
        'categories-failed-names'  => []
    ];
    private $row = [
        'SKU'                => '',
        'MainImageURL'       => '',
        'Category'           => '',
        'Manufacturer'       => '',
        'ProductName'        => '',
        'ProductDescription' => '',
        'UPC'                => '',
        'InStockQuantity'    => '',
        'ShippingWeight'     => '',
        'Caliber'            => '',
        'Velocity'           => '',
        'WNet'               => '',
        'Shipping'           => '',
        'Exchange'           => '',
        'GST'                => '',
        'Duty'               => '',
        'Markup'             => '',
        'Tax'                => '',
        'Price'              => '',
        'WholesalePrice'     => '',
        'Active'             => ''
    ];
    private $categories = [];
    private $manufacturers = [];

    public function init()
    {
        parent::init();

        if (!Shop::getContextShopID()) {
            $controller = 'AdminPurechoiceImport';
            $id_lang = $this->context->language->id;
            $params = [
                'token'          => Tools::getAdminTokenLite($controller),
                'setShopContext' => 's-' . current(Shop::getContextListShopID())
            ];
            $link = Dispatcher::getInstance()->createUrl($controller, $id_lang, $params, false);
            Tools::redirectAdmin($link);
            die();
        }

        $this->bootstrap = true;
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/jquery.fileupload.min.css');
        $this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/jquery.fileupload-ui.min.css');
        $this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/dataTables.bootstrap.min.css');
        $this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/back.css');

        $this->addJquery();
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/tmpl.min.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/jquery.iframe-transport.min.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/jquery.fileupload.min.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/jquery.fileupload-process.min.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/jquery.fileupload-validate.min.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/jquery.fileupload-ui.min.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/jquery.dataTables.min.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/dataTables.bootstrap.min.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/back.js');
    }

    public function initContent()
    {
        parent::initContent();
        // execute below code only for non ajax call
        $ajax = (int)Tools::getValue('ajax', '0');
        if ($ajax === 1) {
            return;
        }

        $id_lang = $this->context->language->id;
        $id_root_category = Category::getRootCategory($id_lang)->id;
        $categories = Category::getChildren($id_root_category, $id_lang);
        foreach ($categories as &$category) {
            $category_option = CategoryOption::getCategoryOptionByCategory($category['id_category']);
            $category['duty'] = ($category_option === false) ? 0 : $category_option->duty;
            $category['shipping'] = ($category_option === false) ? 0 : $category_option->shipping;
            $category['width'] = ($category_option === false) ? -1 : $category_option->width;
            $category['height'] = ($category_option === false) ? -1 : $category_option->height;
            $category['depth'] = ($category_option === false) ? -1 : $category_option->depth;
            $category['show_widget'] = ($category_option === false) ? 0 : $category_option->show_widget;
        }

        $options = json_decode(Configuration::get('PURECHOICE_OPTIONS'), true);
        $options = array_map('html_entity_decode', $options);
        
        $this->context->smarty->assign([
            'module_views_dir' => _PS_ROOT_DIR_ . '/modules/' . $this->module->name . '/views/',
            'taxes'            => TaxRulesGroup::getTaxRulesGroups(),
            'options'          => $options,
            'categories'       => $categories,
            'caliber_all'      => ProductOption::getAllDistinctCaliberValues(),
            'velocity_all'     => ProductOption::getAllDistinctVelocityValues()
        ]);

        $this->setTemplate('configure.tpl');
    }

    public function displayAjax()
    {
        $method = Tools::getValue('method', false);
        $header = Tools::getValue('header', 'json');

        if ($method !== false && method_exists($this, $method)) {
            if ($header == 'json') {
                header('Content-Type: application/json');
                echo Tools::jsonEncode($this->$method());
            } else {
                echo $this->$method();
            }
        }
        die();
    }

    public function processAttachment()
    {
        $id_employee = $this->context->cookie->id_employee;
        $token = Tools::getAdminToken('AdminPurechoiceImport' . (int)Tab::getIdFromClassName('AdminPurechoiceImport') . (int)$id_employee);
        $url = 'index.php?controller=AdminPurechoiceImport&token=' . $token . '&ajax=1';

        $uploads = new PIUploadHandler(
            [
                'upload_dir' => _PS_ADMIN_DIR_ . '/import/' . $this->module->name . '/',
                'script_url' => $url . '&method=processAttachment',
                'upload_url' => $url . '&method=processAttachment&header=txt&download=1&file=',
                'param_name' => 'files'
            ]
        );

        return $uploads->getResponse();
    }

    public function getProducts()
    {
        $file = _PS_ADMIN_DIR_ . '/import/' . $this->module->name . '/' . trim(Tools::getValue('file', ''));
        $start = (int)Tools::getValue('start', 0);
        $length = (int)Tools::getValue('length', 10);
        $results = $this->getProductsByFileAndSKU($file, null, $start, $length);
        
        return [
            'recordsTotal'    => $results['total'],
            'recordsFiltered' => $results['total'],
            'data'            => $results['table']
        ];
    }

    public function getProductsImported()
    {
        $file = _PS_ADMIN_DIR_ . '/import/' . $this->module->name . '/import-rows.tmp';
        $start = (int)Tools::getValue('start', 0);
        $length = (int)Tools::getValue('length', 10);
        $results = $this->getProductsByFileAndSKU($file, null, $start, $length);
        
        return [
            'recordsTotal'    => $results['total'],
            'recordsFiltered' => $results['total'],
            'data'            => $results['table']
        ];
    }

    public function productImportByFile()
    {
        $file = _PS_ADMIN_DIR_ . '/import/' . $this->module->name . '/' . trim(Tools::getValue('file', ''));
        
        $results = $this->getProductsByFileAndSKU($file);
        
        $this->writeImportByFileStatus(['total-rows' => $results['total']]);
        $this->writeImportByFileRow(true);
        $this->categories = [];
        $this->manufacturers = [];

        if (count($results['table']) > 0) {
            foreach ($results['table'] as $data) {
                $this->resetImportByFileRow();

                $data['exchange'] = (float)Tools::getValue('exchange', 0);
                $data['gst'] = (float)Tools::getValue('gst', 0);
                $data['markup'] = (float)Tools::getValue('markup', 0);
                $data['tax'] = (int)Tools::getValue('tax', 0);
                $data['velocity'] = (float)Tools::getValue('velocity', 0);
                $data['exclude'] = trim(Tools::getValue('exclude', ''));
                $data['catvelgtcat'] = (int)Tools::getValue('catvelgtcat', 0);
                $data['catvelgtvel'] = (float)Tools::getValue('catvelgtvel', 0);

                $keywords = $data['exclude'];
                $has_keyword = false;
                if ($keywords !== '') {
                    $keywords = array_map('trim', preg_split("/[\n,]/", $data['exclude']));
                    foreach ($keywords as $keyword) {
                        if (stripos($data['ProductName'], $keyword) !== false ||
                            stripos($data['ProductDescription1'], $keyword) !== false ||
                            stripos($data['SKU'], $keyword) !== false
                            //strcasecmp($data['SKU'], $keyword) === 0
                        ) {
                            $has_keyword = true;
                            break;
                        }
                    }
                }

                if (strcasecmp($data['Parentage'], 'model') === 0 || $has_keyword) {
                    $this->productDeleteBySKU($data['SKU']);
                    $this->writeImportByFileStatus([
                        'products-excluded'      => $this->statistics['products-excluded'] + 1,
                        'products-excluded-skus' => array_merge($this->statistics['products-excluded-skus'], [$data['SKU']])
                    ]);
                    $this->row['SKU'] = '##exclude##' . $data['SKU'];
                } else {
                    $this->rowImport($data);
                }

                $this->writeImportByFileStatus(['rows-processed' => $this->statistics['rows-processed'] + 1]);
                $this->writeImportByFileRow();
            }
        }

        return [
            'status'     => 'success',
            'message'    => 'Product import processed successfully!',
            'statistics' => $this->getImportByFileStatus()
        ];
    }

    public function productImportByFileAndSKU()
    {
        $file = _PS_ADMIN_DIR_ . '/import/' . $this->module->name . '/' . trim(Tools::getValue('file', ''));
        $sku = trim(Tools::getValue('sku', ''));
        $results = $this->getProductsByFileAndSKU($file, $sku);

        if (count($results['table']) > 0) {
            $data = reset($results['table']);

            $data['exchange'] = (float)Tools::getValue('exchange', 0);
            $data['gst'] = (float)Tools::getValue('gst', 0);
            $data['markup'] = (float)Tools::getValue('markup', 0);
            $data['tax'] = (int)Tools::getValue('tax', 0);
            $data['velocity'] = (float)Tools::getValue('velocity', 0);
            $data['exclude'] = trim(Tools::getValue('exclude', ''));
            $data['catvelgtcat'] = (int)Tools::getValue('catvelgtcat', 0);
            $data['catvelgtvel'] = (float)Tools::getValue('catvelgtvel', 0);

            $keywords = $data['exclude'];
            $has_keyword = false;
            if ($keywords !== '') {
                $keywords = array_map('trim', preg_split("/[\n,]/", $data['exclude']));
                foreach ($keywords as $keyword) {
                    if (stripos($data['ProductName'], $keyword) !== false ||
                        stripos($data['ProductDescription1'], $keyword) !== false ||
                        stripos($data['SKU'], $keyword) !== false
                        //strcasecmp($data['SKU'], $keyword) === 0
                    ) {
                        $has_keyword = true;
                        break;
                    }
                }
            }

            if (strcasecmp($data['Parentage'], 'model') === 0 || $has_keyword) {
                $this->productDeleteBySKU($data['SKU']);

                return [
                    'status'  => 'danger',
                    'message' => 'Exclude keyword found or product is model.'
                ];
            } else {
                $import = $this->rowImport($data);
                if ($import === true) {
                    return [
                        'status'  => 'success',
                        'message' => 'Product "' . $data['ProductName'] . '" imported successfully!',
                        'row'     => $this->row
                    ];
                }
            }
        }

        return [
            'status'  => 'danger',
            'message' => 'Product imported failed.'
        ];
    }

    public function categoryImportByFile()
    {
        $file = _PS_ADMIN_DIR_ . '/import/' . $this->module->name . '/' . trim(Tools::getValue('file', ''));
        $results = $this->getProductsByFileAndSKU($file);
        $this->writeImportByFileStatus(['total-rows' => $results['total']]);

        if (count($results['table']) > 0) {
            foreach ($results['table'] as $data) {
                $this->writeImportByFileStatus(['rows-processed' => $this->statistics['rows-processed'] + 1]);

                $import = $this->categoryImport($data['Category']);
                if ($import === 0) {
                    $this->writeImportByFileStatus([
                        'categories-failed'       => $this->statistics['categories-failed'] + 1,
                        'categories-failed-names' => array_merge($this->statistics['categories-failed-names'], [$data['Category']])
                    ]);
                }
            }
        }

        return [
            'status'     => 'success',
            'message'    => 'Category import processed successfully!',
            'statistics' => $this->getImportByFileStatus()
        ];
    }

    public function getImportByFileStatus()
    {
        return Tools::jsonDecode(file_get_contents(_PS_ADMIN_DIR_ . '/import/' . $this->module->name . '/import-status.tmp'));
    }

    public function setOptions()
    {
        $options = [
            'exchange'         => trim(Tools::getValue('exchange', 1.26)),
            'gst'              => trim(Tools::getValue('gst', 5)),
            'markup'           => trim(Tools::getValue('markup', 15)),
            'tax'              => trim(Tools::getValue('tax', 0)),
            'velocity'         => trim(Tools::getValue('velocity', 366)),
            'exclude'          => trim(Tools::getValue('exclude', 'silencer, moderator, whisper, suppressor')),
            'caliber_options'  => trim(Tools::getValue('caliber_options', '')),
            'velocity_options' => trim(Tools::getValue('velocity_options', '')),
            'catvelgtcat'      => trim(Tools::getValue('catvelgtcat', 0)),
            'catvelgtvel'      => trim(Tools::getValue('catvelgtvel', 366))
        ];

        $options['exclude'] = implode("\n", array_filter(array_map('trim', preg_split("/[\n,]/", $options['exclude']))));

        $options = array_map('htmlentities', $options);
        Configuration::updateValue('PURECHOICE_OPTIONS', json_encode($options));
        $options = json_decode(Configuration::get('PURECHOICE_OPTIONS'), true);
        $options = array_map('html_entity_decode', $options);

        return [
            'status'           => 'success',
            'message'          => 'Options saved successfully!',
            'exchange'         => $options['exchange'],
            'gst'              => $options['gst'],
            'markup'           => $options['markup'],
            'tax'              => $options['tax'],
            'velocity'         => $options['velocity'],
            'exclude'          => $options['exclude'],
            'caliber_options'  => $options['caliber_options'],
            'velocity_options' => $options['velocity_options'],
            'catvelgtcat'      => $options['catvelgtcat'],
            'catvelgtvel'      => $options['catvelgtvel']
        ];
    }

    public function setCategoryOption()
    {

        $id_category = (int)Tools::getValue('id_category', 0);
        $duty = (float)Tools::getValue('duty', 0);
        $shipping = (float)Tools::getValue('shipping', 0);
        $width = (float)Tools::getValue('width', 0);
        $height = (float)Tools::getValue('height', 0);
        $depth = (float)Tools::getValue('depth', 0);
        $show_widget = (int)Tools::getValue('show_widget', 0);

        $category_option = CategoryOption::getCategoryOptionByCategory($id_category);
        if ($category_option === false) {
            $category_option = new CategoryOption();
            $category_option->id_category = $id_category;
        }

        $category = new Category($id_category);
        $products = $category->getProducts($this->context->language->id, 1, 999999);
        $product_ids = array('width' => array(), 'height' => array(), 'depth' => array());
        foreach ($products as $product) {
            if ($width >= 0) {
                $product_ids['width'][] = $product['id_product'];
            }
            if ($height >= 0) {
                $product_ids['height'][] = $product['id_product'];
            }
            if ($depth >= 0) {
                $product_ids['depth'][] = $product['id_product'];
            }
        }

        if (count($product_ids['width'])) {
            Db::getInstance()->query('UPDATE ' . _DB_PREFIX_ . 'product SET width = ' . $width . ' WHERE id_product IN (' . implode(',', $product_ids['width']) . ');');
        }
        if (count($product_ids['height'])) {
            Db::getInstance()->query('UPDATE ' . _DB_PREFIX_ . 'product SET height = ' . $height . ' WHERE id_product IN (' . implode(',', $product_ids['height']) . ');');
        }
        if (count($product_ids['depth'])) {
            Db::getInstance()->query('UPDATE ' . _DB_PREFIX_ . 'product SET depth = ' . $depth . ' WHERE id_product IN (' . implode(',', $product_ids['depth']) . ');');
        }

        $category_option->duty = $duty;
        $category_option->shipping = $shipping;
        $category_option->width = $width;
        $category_option->height = $height;
        $category_option->depth = $depth;
        $category_option->show_widget = $show_widget;
        $category_option->save();


        return [
            'status'      => 'success',
            'message'     => 'Category duty saved successfully!',
            'duty'        => $category_option->duty,
            'shipping'    => $category_option->shipping,
            'width'       => $category_option->width,
            'height'      => $category_option->height,
            'depth'       => $category_option->depth,
            'show_widget' => $category_option->show_widget
        ];
    }

    public function deleteExcludedProducts()
    {
        $options = json_decode(Configuration::get('PURECHOICE_OPTIONS'), true);
        $options = array_map('html_entity_decode', $options);
        $keywords = array_filter(array_map('trim', preg_split("/[\n,]/", $options['exclude'])));
        $id_lang = $this->context->language->id;
        $count = 0;

        foreach ($keywords as $keyword) {
            $products = Product::searchByName($id_lang, $keyword);
            foreach ($products as $product) {
                $product_obj = new Product((int)$product['id_product'], false, $id_lang);
                $product_obj->delete();
                $count++;
            }
        }

        return [
            'status'  => 'success',
            'message' => $count . ' excluded products deleted.'
        ];
    }

    public function disableVelocityProducts()
    {
        $options = json_decode(Configuration::get('PURECHOICE_OPTIONS'), true);
        $options = array_map('html_entity_decode', $options);
        $velocity = $options['velocity'];
        $catvelgtvel = $options['catvelgtvel'];
        $catvelgtcat = $options['catvelgtcat'];
        $product_options = ProductOption::getAll();
        $id_lang = $this->context->language->id;
        $count = 0;

        foreach ($product_options as $product_option) {
            $product_obj = new Product((int)$product_option['id_product'], false, $id_lang);
            if (trim($product_option['velocity']) !== ''
                && ($product_option['velocity'] < $velocity
                    || ($product_option['velocity'] > $catvelgtvel
                        && ($catvelgtcat == $product_obj->id_category_default || $catvelgtcat == 0)))) {
                if ($product_obj->active) {
                    $product_obj->active = 0;
                    $product_obj->save();
                    $count++;
                }
            } else {
                if (!$product_obj->active) {
                    $product_obj->active = 1;
                    $product_obj->save();
                    $count++;
                }
            }
        }

        return [
            'status'  => 'success',
            'message' => $count . ' products updated.'
        ];
    }
    
    private function getProductsByFileAndSKU($file, $sku = null, $start = null, $length = null)
    {
 
        $table = [];
        $total = 0;
        if ($file !== '' && file_exists($file) && is_file($file)) {
            if (($handle = fopen($file, 'r')) !== false) {
                $line = -1;
                $head = [];
                while (($data = fgetcsv($handle, 50000, "\t")) !== false) {
                    if (!count($head)) {
                        foreach ($data as $key => $value) {
                            $head[$value] = $key;
                        }
                        $head = array_flip($head);
                        continue;
                    }

                    $line++;

                    // if current line is less than start skip
                    if ($start !== null && $line < $start) {
                        continue;
                    }

                    // prepare row
                    $row = [];
                    foreach ($data as $key => $value) {
                        $row[$head[$key]] = trim(utf8_encode($value));
                    }
                    // if current sku is not equal to sku skip
                    if ($sku !== null && $row['SKU'] !== $sku) {
                        continue;
                    }

                    // collect row
                    $table[] = $row;

                    // if total collected rows is equal to length abort
                    if ($length !== null && count($table) === $length) {
                        break;
                    }
                }
                fclose($handle);
            }
            $total = count(file($file)) - 1;
        }

        return [
            'total' => $total,
            'table' => $table
        ];
    }

    private function rowImport($data)
    {
        /* category import */
        $id_category = $this->categoryImport($data['Category']);

        /* product category duty */
        $category_option = CategoryOption::getCategoryOptionByCategory($id_category);
        $data['duty'] = ($category_option === false) ? 0 : $category_option->duty;
        $data['shipping'] = ($category_option === false) ? 0 : $category_option->shipping;

        /* manufacturer import */
        $id_manufacturer = $this->manufacturerImport($data['Manufacturer']);

        /* product import */
        $id_product = $this->productImport($data, $id_manufacturer, $id_category);
        if ($id_product === false) {
            return false;
        }

        /* product options import */
        $this->productOptionImport($data, $id_product);

        /* images import */
        $this->imageImport($data['MainImageURL'], $id_product);

        return true;
    }

    private function categoryImport($name)
    {
        $id_lang = $this->context->language->id;
        $id_category = 0;
        $name = self::getCatalogNameByString($name);
        if ($name != '') {
            $category_search = array_search($name, $this->categories);
            if ($category_search !== false) {
                return $category_search;
            }

            $id_root_category = Category::getRootCategory($id_lang)->id;
            $category = Category::searchByNameAndParentCategoryId($id_lang, $name, $id_root_category);
            $id_category = (isset($category['id_category'])) ? $category['id_category'] : 0;
            if ($id_category === 0) {
                $category = new Category(null, $id_lang);
                $category->name = $name;
                $category->link_rewrite = Tools::link_rewrite($name);
                $category->id_parent = $id_root_category;
                $category->active = 1;
                $validate_controller = $category->validateController();
                if (count($validate_controller) == 0 && $category->add()) {
                    $id_category = $category->id;
                    $this->writeImportByFileStatus(['categories-created' => $this->statistics['categories-created'] + 1]);
                }
            }
        }

        if ($id_category !== 0) {
            $this->categories[$id_category] = $name;
        }

        return $id_category;
    }

    private function manufacturerImport($name)
    {
        $id_lang = $this->context->language->id;
        $id_manufacturer = 0;
        $name = self::getCatalogNameByString($name);
        if ($name != '') {
            $manufacturer_search = array_search($name, $this->manufacturers);
            if ($manufacturer_search !== false) {
                return $manufacturer_search;
            }

            $id_manufacturer = Manufacturer::getIdByName($name);
            $id_manufacturer = ($id_manufacturer !== false) ? $id_manufacturer : 0;
            if ($id_manufacturer === 0) {
                $manufacturer = new Manufacturer(null, $id_lang);
                $manufacturer->name = $name;
                $manufacturer->link_rewrite = Tools::link_rewrite($name);
                $manufacturer->active = 1;
                $validate_controller = $manufacturer->validateController();
                if (count($validate_controller) == 0 && $manufacturer->add()) {
                    $id_manufacturer = $manufacturer->id;
                    $this->writeImportByFileStatus(['manufacturers-created' => $this->statistics['manufacturers-created'] + 1]);
                }
            }
        }

        if ($id_manufacturer !== 0) {
            $this->manufacturers[$id_manufacturer] = $name;
        }

        return $id_manufacturer;
    }

    private function productDeleteBySKU($sku)
    {
        $id_lang = $this->context->language->id;
        $id_product = (int)self::getProductIdByReference($sku);
        if ($id_product !== 0) {
            $product = new Product((int)$id_product, false, $id_lang);
            $product->delete();
        }
    }

    private function productImport($data, $id_manufacturer, $id_category)
    {
        $id_lang = $this->context->language->id;
        $id_product = (int)self::getProductIdByReference($data['SKU']);
        if ($id_product !== 0) {
            $action = 'update';
            $product = new Product((int)$id_product, false, $id_lang);
            $product->id = (int)$id_product;
            $product->force_id = true;
        } else {
            $action = 'add';
            $product = new Product(null, false, $id_lang);
            $product->reference = $data['SKU'];
        }

        $this->row['SKU'] = $product->reference;

        // calculate price
        $price = (float)$data['WNet'];
        $price = $price * (float)$data['exchange'];
        $price = $price + $price * (float)$data['gst'] * 1.05 / 100;
        $price = $price + $price * (float)$data['duty'] / 100 + (float)$data['shipping'];
        $wholesale_price = number_format($price, 6, '.', '');
        $price = number_format($price + $price * (float)$data['markup'] / 100, 6, '.', '');

        // check whether active or not
        $velocity = (float)trim($data['Velocity'], "a..zA..Z \t\n\r\0\x0B");
        $active = ($data['Velocity'] !== '' && $velocity < $data['velocity']) ? 0 : 1;
        $active = ($data['InStockQuantity'] == 0 ) ? 0 : 1;
        $active = ($data['Velocity'] !== '' && $velocity > $data['catvelgtvel'] && ($data['catvelgtcat'] === (int)$id_category || $data['catvelgtcat'] === 0)) ? 0 : $active;

        // start check whether data changed
        $update = false;
        $check_needs_update_category = false;
        $check_needs_update_quantity = false;
        if ($action === 'update') {
            $update = self::checkNeedsUpdate(self::getCatalogNameByString(substr($data['ProductName'], 0, 128)), $product->name, 'ProductName', 'string') || $update;
            $update = self::checkNeedsUpdate(Tools::purifyHTML($data['ProductDescription1']), $product->description, 'ProductDescription', 'string') || $update;
            $update = self::checkNeedsUpdate((float)$data['ShippingWeight'], $product->weight, 'ShippingWeight', 'float') || $update;
            $check_needs_update_category = self::checkNeedsUpdate($id_category, $product->id_category_default, 'Category', 'int');
            $update = $check_needs_update_category || $update;
            $update = self::checkNeedsUpdate($id_manufacturer, $product->id_manufacturer, 'Manufacturer', 'int') || $update;
            $update = self::checkNeedsUpdate((int)$data['tax'], $product->id_tax_rules_group, 'Tax', 'int') || $update;
            $update = self::checkNeedsUpdate($price, $product->price, 'Price', 'float') || $update;
            $update = self::checkNeedsUpdate($wholesale_price, $product->wholesale_price, 'WholesalePrice', 'float') || $update;
            $update = self::checkNeedsUpdate($active, $product->active, 'Active', 'int') || $update;
            $check_needs_update_quantity = self::checkNeedsUpdate((int)$data['InStockQuantity'], StockAvailable::getQuantityAvailableByProduct($product->id, 0), 'InStockQuantity', 'int');
            $update = $check_needs_update_quantity || $update;
        }
        // end check whether data changed

        $product->name = self::getCatalogNameByString(substr($data['ProductName'], 0, 128));
        $product->link_rewrite = Tools::link_rewrite(substr($data['ProductName'], 0, 128));
        $product->description = $data['ProductDescription1'];
        $product->meta_description = '';
        $product->weight = (float)$data['ShippingWeight'];
        $product->id_category_default = $id_category;
        $product->id_manufacturer = $id_manufacturer;
        $product->id_tax_rules_group = (int)$data['tax'];
        $product->price = ($price == 0) ? '0' : $price;
        $product->wholesale_price = ($wholesale_price == 0) ? '0' : $wholesale_price;
        $product->active = $active;
        $validate_controller = $product->validateController();
        
        if (count($validate_controller) == 0) {
            if ($action === 'add') {
                $product->add();
                $productcreateddata = $data['Category'].":|:".$data['SKU'].":|:".$data['ProductDescription1'].":|:".$velocity;
                $this->writeImportByFileStatus(['products-created' => $this->statistics['products-created'] + 1]);
                $this->writeImportByFileStatus(['products-created-data' => array_merge($this->statistics['products-created-data'], [$productcreateddata])]);
                $this->row['SKU'] = '##add##' . $product->reference;
            } elseif ($update) {
                $product->save();
                $this->writeImportByFileStatus(['products-updated' => $this->statistics['products-updated'] + 1]);
                $this->row['SKU'] = '##update##' . $product->reference;
            }
            if ($action === 'add' || ($action === 'update' && $check_needs_update_category)) {
                $product->addToCategories([$id_category]);
            }
            if ($action === 'add' || ($action === 'update' && $check_needs_update_quantity)) {
                StockAvailable::setQuantity($product->id, 0, (int)$data['InStockQuantity']);
            }
            if (($action === 'add' || $update) && in_array($product->visibility, array('both', 'search')) && Configuration::get('PS_SEARCH_INDEXATION')) {
                Search::indexation(false, $product->id);
            }
        } else {
            $this->writeImportByFileStatus([
                'products-failed'          => $this->statistics['products-failed'] + 1,
                'products-failed-skus'     => array_merge($this->statistics['products-failed-skus'], [$data['SKU']]),
                'products-failed-messages' => array_merge($this->statistics['products-failed-messages'], [$data['SKU'] => $validate_controller])
                
            ]);
            $this->row['SKU'] = '##fail##' . $product->reference;

            return false;
        }

        return $product->id;
    }

    private function productOptionImport($data, $id_product)
    {
        $data['Caliber'] = preg_replace('/[^0-9\.]+/', '', $data['Caliber']);
        $data['Velocity'] = preg_replace('/[^0-9\.]+/', '', $data['Velocity']);

        $product_option = ProductOption::getProductOptionByProduct($id_product);
        if ($product_option !== false) {
            $action = 'update';
        } else {
            $action = 'add';
            $product_option = new ProductOption();
            $product_option->id_product = $id_product;
        }

        // start check whether data changed
        $update = false;
        if ($action === 'update') {
            $update = self::checkNeedsUpdate($data['UPC'], $product_option->upc, 'UPC', 'string') || $update;
            $update = self::checkNeedsUpdate($data['Caliber'], $product_option->caliber, 'Caliber', 'string') || $update;
            $update = self::checkNeedsUpdate($data['Velocity'], $product_option->velocity, 'Velocity', 'string') || $update;
            $update = self::checkNeedsUpdate($data['MainImageURL'], $product_option->image_url, 'MainImageURL', 'string') || $update;
            $update = self::checkNeedsUpdate((float)$data['WNet'], $product_option->wnet, 'WNet', 'float') || $update;
            $update = self::checkNeedsUpdate((float)$data['shipping'], $product_option->shipping, 'Shipping', 'float') || $update;
            $update = self::checkNeedsUpdate((float)$data['exchange'], $product_option->exchange, 'Exchange', 'float') || $update;
            $update = self::checkNeedsUpdate((float)$data['gst'], $product_option->gst, 'GST', 'float') || $update;
            $update = self::checkNeedsUpdate((float)$data['duty'], $product_option->duty, 'Duty', 'float') || $update;
            $update = self::checkNeedsUpdate((float)$data['markup'], $product_option->markup, 'Markup', 'float') || $update;
        }
        // end check whether data changed

        $product_option->upc = $data['UPC'];
        $product_option->caliber = $data['Caliber'];
        $product_option->velocity = $data['Velocity'];
        $product_option->image_url = $data['MainImageURL'];
        $product_option->wnet = (float)$data['WNet'];
        $product_option->shipping = (float)$data['shipping'];
        $product_option->exchange = (float)$data['exchange'];
        $product_option->gst = (float)$data['gst'];
        $product_option->duty = (float)$data['duty'];
        $product_option->markup = (float)$data['markup'];
        if ($action === 'add') {
            $product_option->add();
        } elseif ($update) {
            $product_option->save();
            $product_has_add = preg_match('/^##add##/', $this->row['SKU']);
            $product_has_update = preg_match('/^##update##/', $this->row['SKU']);
            if (!$product_has_add && !$product_has_update) {
                $this->row['SKU'] = '##update##' . $this->row['SKU'];
            }
        }
    }

    private function imageImport($image_url, $id_product)
    {
        $product_has_add = preg_match('/^##add##/', $this->row['SKU']);
        $image_has_update = preg_match('/^##update##/', $this->row['MainImageURL']);

        if ($product_has_add || $image_has_update) {
            $id_lang = $this->context->language->id;
            if (trim($image_url) != '') {
                $product = new Product($id_product, false, $id_lang);
                $product->deleteImages();
                $image_url = trim($image_url);
                if (!empty($image_url)) {
                    $image_url = str_replace(' ', '%20', $image_url);
                    $image = new Image(null, $id_lang);
                    $image->id_product = (int)$product->id;
                    $image->position = Image::getHighestPosition($product->id) + 1;
                    $image->cover = true;
                    if (($field_error = $image->validateFields(UNFRIENDLY_ERROR, true)) === true && ($lang_field_error = $image->validateFieldsLang(UNFRIENDLY_ERROR, true)) === true && $image->add()) {
                        if (!self::copyImg($product->id, $image->id, $image_url, 'products')) {
                            $image->delete();
                        }
                    }
                }
            }
        }
    }

    private static function getProductIdByReference($reference)
    {
        $sql = new DbQuery();
        $sql->select('id_product');
        $sql->from('product');
        $sql->where('reference = \'' . $reference . '\'');
        $id_product = Db::getInstance()->getValue($sql);

        if ($id_product === false) {
            return false;
        } else {
            return $id_product;
        }
    }

    private static function copyImg($id_entity, $id_image = null, $url, $entity = 'products', $regenerate = true)
    {
        $tmpfile = tempnam(_PS_TMP_IMG_DIR_, 'ps_import');
        $watermark_types = explode(',', Configuration::get('WATERMARK_TYPES'));

        switch ($entity) {
            default:
            case 'products':
                $image_obj = new Image($id_image);
                $path = $image_obj->getPathForCreation();
                break;
            case 'categories':
                $path = _PS_CAT_IMG_DIR_ . (int)$id_entity;
                break;
            case 'manufacturers':
                $path = _PS_MANU_IMG_DIR_ . (int)$id_entity;
                break;
            case 'suppliers':
                $path = _PS_SUPP_IMG_DIR_ . (int)$id_entity;
                break;
        }

        $url = urldecode(trim($url));
        $parced_url = parse_url($url);

        if (isset($parced_url['path'])) {
            $uri = ltrim($parced_url['path'], '/');
            $parts = explode('/', $uri);
            foreach ($parts as &$part) {
                $part = rawurlencode($part);
            }
            unset($part);
            $parced_url['path'] = '/' . implode('/', $parts);
        }

        if (isset($parced_url['query'])) {
            $query_parts = array();
            parse_str($parced_url['query'], $query_parts);
            $parced_url['query'] = http_build_query($query_parts);
        }

        if (!function_exists('http_build_url')) {
            require_once(_PS_TOOL_DIR_ . 'http_build_url/http_build_url.php');
        }

        $url = http_build_url('', $parced_url);

        $orig_tmpfile = $tmpfile;

        if (Tools::copy($url, $tmpfile)) {
            // Evaluate the memory required to resize the image: if it's too much, you can't resize it.
            if (!ImageManager::checkImageMemoryLimit($tmpfile)) {
                @unlink($tmpfile);

                return false;
            }

            $tgt_width = $tgt_height = 0;
            $src_width = $src_height = 0;
            $error = 0;
            ImageManager::resize($tmpfile, $path . '.jpg', null, null, 'jpg', false, $error, $tgt_width, $tgt_height, 5,
                $src_width, $src_height);
            $images_types = ImageType::getImagesTypes($entity, true);

            if ($regenerate) {
                $previous_path = null;
                $path_infos = array();
                $path_infos[] = array($tgt_width, $tgt_height, $path . '.jpg');
                foreach ($images_types as $image_type) {
                    $tmpfile = self::getBestPath($image_type['width'], $image_type['height'], $path_infos);

                    if (ImageManager::resize($tmpfile, $path . '-' . stripslashes($image_type['name']) . '.jpg', $image_type['width'],
                        $image_type['height'], 'jpg', false, $error, $tgt_width, $tgt_height, 5,
                        $src_width, $src_height)
                    ) {
                        // the last image should not be added in the candidate list if it's bigger than the original image
                        if ($tgt_width <= $src_width && $tgt_height <= $src_height) {
                            $path_infos[] = array($tgt_width, $tgt_height, $path . '-' . stripslashes($image_type['name']) . '.jpg');
                        }
                        if ($entity == 'products') {
                            if (is_file(_PS_TMP_IMG_DIR_ . 'product_mini_' . (int)$id_entity . '.jpg')) {
                                unlink(_PS_TMP_IMG_DIR_ . 'product_mini_' . (int)$id_entity . '.jpg');
                            }
                            if (is_file(_PS_TMP_IMG_DIR_ . 'product_mini_' . (int)$id_entity . '_' . (int)Context::getContext()->shop->id . '.jpg')) {
                                unlink(_PS_TMP_IMG_DIR_ . 'product_mini_' . (int)$id_entity . '_' . (int)Context::getContext()->shop->id . '.jpg');
                            }
                        }
                    }
                    if (in_array($image_type['id_image_type'], $watermark_types)) {
                        Hook::exec('actionWatermark', array('id_image' => $id_image, 'id_product' => $id_entity));
                    }
                }
            }
        } else {
            @unlink($orig_tmpfile);

            return false;
        }
        unlink($orig_tmpfile);

        return true;
    }

    private static function getBestPath($tgt_width, $tgt_height, $path_infos)
    {
        $path_infos = array_reverse($path_infos);
        $path = '';
        foreach ($path_infos as $path_info) {
            list($width, $height, $path) = $path_info;
            if ($width >= $tgt_width && $height >= $tgt_height) {
                return $path;
            }
        }

        return $path;
    }

    private static function getCatalogNameByString($string)
    {
        return str_ireplace(
            ['&amp;', '&quot;', '&lt;', '&gt;', '<', '>', ';', '=', '#', '{', '}'],
            ['&', '"', '-', '-', '-', '-', '-', '-', '-', '-', '-'],
            strip_tags($string)
        );
    }

    private function checkNeedsUpdate($new, $exsting, $key, $type = 'string')
    {
        $update = false;
        switch ($type) {
            case 'string':
                if (strcasecmp($new, $exsting) !== 0) {
                    $update = true;
                }
                break;
            case 'int':
            case 'float':
                if ($new != $exsting) {
                    $update = true;
                }
                break;
        }

        $this->row[$key] = ($update) ? '##update##' . $new : $new;

        return $update;
    }

    private function writeImportByFileStatus($statistics)
    {
        $this->statistics = array_merge($this->statistics, $statistics);
        $statistics = $this->statistics;
        $statistics['products-excluded-skus'] = (count($statistics['products-excluded-skus']) > 0) ? implode(', ', $statistics['products-excluded-skus']) : '';
        $statistics['products-failed-skus'] = (count($statistics['products-failed-skus']) > 0) ? implode(', ', $statistics['products-failed-skus']) : '';
        $statistics['categories-failed-names'] = (count($statistics['categories-failed-names']) > 0) ? implode(', ', $statistics['categories-failed-names']) : '';
        $statistics['products-created-data'] = (count($statistics['products-created-data']) > 0) ? implode(':||:', $statistics['products-created-data']) : '';
        file_put_contents(_PS_ADMIN_DIR_ . '/import/' . $this->module->name . '/import-status.tmp', json_encode($statistics, JSON_PRETTY_PRINT));
    }

    private function resetImportByFileRow()
    {
        $this->row = [
            'SKU'                => '',
            'MainImageURL'       => '',
            'Category'           => '',
            'Manufacturer'       => '',
            'ProductName'        => '',
            'ProductDescription' => '',
            'UPC'                => '',
            'InStockQuantity'    => '',
            'ShippingWeight'     => '',
            'Caliber'            => '',
            'Velocity'           => '',
            'WNet'               => '',
            'Shipping'           => '',
            'Exchange'           => '',
            'GST'                => '',
            'Duty'               => '',
            'Markup'             => '',
            'Tax'                => '',
            'Price'              => '',
            'WholesalePrice'     => '',
            'Active'             => ''
        ];
    }

    private function writeImportByFileRow($header = false)
    {
        $row = implode("\t", ($header) ? array_keys($this->row) : $this->row);

        $write = false;
        if ($header) {
            $write = true;
        } elseif (stripos($row, '##add##') !== false) {
            $write = true;
        } elseif (stripos($row, '##update##') !== false) {
            $write = true;
        } elseif (stripos($row, '##exclude##') !== false) {
            $write = true;
        } elseif (stripos($row, '##fail##') !== false) {
            $write = true;
        }

        if ($write) {
            file_put_contents(_PS_ADMIN_DIR_ . '/import/' . $this->module->name . '/import-rows.tmp', $row . PHP_EOL, (($header) ? null : FILE_APPEND));
        }
    }
}