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

include_once dirname(__FILE__) . '/Helpers.php';

if (!class_exists('KICategories')) {
    /**
     * Class KICategories
     */
    class KICategories
    {
        public static function getCategories($id_lang = null, $id_category_parent = null, $search = '', $id_shop = null)
        {
            $search_filtered = KIHelpers::filterSearchQuery($search, array('id', 'name'));

            if ($id_lang == null) {
                $id_lang = Context::getContext()->language->id;
            }

            $sql = new DbQuery();
            $sql->select('DISTINCT c.`id_category`');
            $sql->from('category', 'c');
            $sql->leftJoin('category_shop', 's', 'c.`id_category` = s.`id_category`');
            $sql->leftJoin('category_lang', 'l', 'c.`id_category` = l.`id_category`');

            if ($search_filtered['id'] != '') {
                $sql->where('c.`id_category` IN (' . (int)$search_filtered['id'] . ')');
            }

            if ($search_filtered['name'] != '') {
                $sql->where('l.`name` LIKE \'%' . pSQL($search_filtered['name']) . '%\'');
            }

            if ($search_filtered['id'] == '' && $search_filtered['name'] == '' && $search_filtered['query'] != '') {
                $sql->where('l.`name` LIKE \'%' . pSQL($search_filtered['query']) . '%\'');
            }

            if ($id_category_parent != null && $search == '') {
                $sql->where('c.`id_parent` = ' . (int)$id_category_parent);
            }

            $sql->where('l.`id_lang` = ' . (int)$id_lang . ' AND s.`id_shop` = l.`id_shop`');

            if ($id_shop != null) {
                $sql->where('s.`id_shop` IN (' . pSQL($id_shop) . ')');
                $sql->where('l.`id_shop` IN (' . pSQL($id_shop) . ')');
            }

            $sql->orderby('c.`id_category` ASC');

            return Db::getInstance()->executeS($sql);
        }

        public static function getChildrenCount($id_category_parent, $id_shop = null)
        {
            $sql = new DbQuery();
            $sql->select('COUNT(DISTINCT c.`id_category`) AS total');
            $sql->from('category', 'c');
            $sql->leftJoin('category_shop', 's', 'c.`id_category` = s.`id_category`');
            $sql->leftJoin('category_lang', 'l', 'c.`id_category` = l.`id_category`');

            $sql->where('c.`id_parent` = ' . (int)$id_category_parent . ' AND s.`id_shop` = l.`id_shop`');

            if ($id_shop != null) {
                $sql->where('s.`id_shop` IN (' . pSQL($id_shop) . ')');
                $sql->where('l.`id_shop` IN (' . pSQL($id_shop) . ')');
            }

            return Db::getInstance()->getValue($sql);
        }
    }
}
