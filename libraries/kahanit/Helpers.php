<?php
/**
 * Kahanit Framework for PrestaShop Modules by Kahanit
 *
 * Kahanit Framework by Kahanit(http://www.kahanit.com)
 * is licensed under a Creative Creative Commons Attribution-NoDerivatives 4.0
 * International License. Based on a work at http://www.kahanit.com.
 * Permissions beyond the scope of this license may be available at http://www.kahanit.com.
 * To view a copy of this license, visit http://creativecommons.org/licenses/by-nd/4.0/.
 *
 * @author    Amit Sidhpura <amit@kahanit.com>
 * @copyright 2016 Kahanit
 * @license   http://creativecommons.org/licenses/by-nd/4.0/
 */

if (!class_exists('KIHelpers')) {
    /**
     * Class KIHelpers
     */
    class KIHelpers
    {
        public static function installModuleTab($tabName, $tabClass, $moduleName, $tabParentClass, $showTab = true)
        {
            $tab = new Tab();

            foreach (Language::getLanguages() as $language) {
                $tab->name[$language['id_lang']] = pSQL($tabName);
            }

            $tab->class_name = pSQL($tabClass);
            $tab->module = pSQL($moduleName);
            $tab->id_parent = (int)Tab::getIdFromClassName($tabParentClass);
            $tab->active = (int)$showTab;

            if (!$tab->save()) {
                return false;
            }

            return true;
        }

        public static function uninstallModuleTab($tabClass)
        {
            $idTab = Tab::getIdFromClassName($tabClass);

            if ($idTab != 0) {
                $tab = new Tab($idTab);
                $tab->delete();

                return true;
            }

            return false;
        }

        public static function addslashes($str, $enclosed = false)
        {
            if (is_array($str)) {
                foreach ($str as &$item) {
                    $item = self::addslashes($item, $enclosed);
                }

                return $str;
            }

            $search = ["\\", "\0", "\n", "\r", "\x1a", "'", '"'];
            $replace = ["\\\\", "\\0", "\\n", "\\r", "\Z", "\'", '\"'];

            if ($enclosed) {
                $str = self::addslashes($str);
            }

            return str_replace($search, $replace, $str);
        }

        public static function stripslashes($str)
        {
            if (is_array($str)) {
                foreach ($str as &$item) {
                    $item = self::stripslashes($item);
                }

                return $str;
            }

            $search = ["\\\\", "\\0", "\\n", "\\r", "\Z", "\'", '\"'];
            $replace = ["\\", "\0", "\n", "\r", "\x1a", "'", '"'];

            return str_replace($search, $replace, $str);
        }

        public static function filterSearchQuery($search_query = '', $keys = array())
        {
            $search_query = trim($search_query);
            $search_items = array();

            foreach ($keys as $key) {
                $pattern = '/' . $key . '\s*:\s*[^:]*((?=' . implode('\s*:)|(?=', $keys) . '\s*:)|($))/i';
                preg_match($pattern, $search_query, $matches);

                if (isset($matches[0])) {
                    $search_value = preg_replace('/\s+/', ' ', $matches[0]);
                } else {
                    $search_value = '';
                }

                $search_value = explode(':', $search_value);
                $search_items[$key] = end($search_value);
                $search_items[$key] = trim($search_items[$key]);
            }

            $search_items['query'] = $search_query;

            return $search_items;
        }

        public static function getLibrary($library, $module)
        {
            include_once _PS_MODULE_DIR_ . $module->name . '/' . 'libraries/' . $library . '.php';
        }

        public static function getAdminLink($controller, $with_token = true, $params = array())
        {
            $id_lang = Context::getContext()->language->id;

            if ($with_token) {
                $params['token'] = Tools::getAdminTokenLite($controller);
            }

            return Dispatcher::getInstance()->createUrl($controller, $id_lang, $params, false);
        }

        public static function displayAjax($class)
        {
            $app = Tools::getValue('app', false);

            if ($app !== false) {
                $url = explode('/', filter_var(rtrim($app, ' / '), FILTER_SANITIZE_URL));

                if (isset($url[0])) {
                    unset($url[0]);
                }

                if (isset($url[1])) {
                    unset($url[1]);
                }

                if (isset($url[2])) {
                    $method_name = $url[2];
                    unset($url[2]);
                }

                $url = array_values($url);
                $params = array();

                foreach ($url as $key => $value) {
                    if ($key % 2 == 0) {
                        $params[$value] = $url[$key + 1];
                    }
                }

                if (isset($method_name) && !empty($method_name) && method_exists($class, $method_name)) {
                    echo $class->$method_name($params);
                    die();
                }
            }
        }

        public static function shopRedirect()
        {
            $context = Context::getContext();

            if (!Shop::getContextShopID()) {
                $controller = 'AdminTableRateShipping';
                $id_lang = $context->language->id;
                $params = [
                    'token'          => Tools::getAdminTokenLite($controller),
                    'setShopContext' => 's-' . current(Shop::getContextListShopID())
                ];
                $link = Dispatcher::getInstance()->createUrl($controller, $id_lang, $params, false);
                Tools::redirectAdmin($link);
                die();
            }
        }
    }
}
