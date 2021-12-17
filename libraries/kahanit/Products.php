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

if (!class_exists('KIProducts')) {
    /**
     * Class KIProducts
     */
    class KIProducts
    {
        public static function getProducts($id_lang = null, $id_category = null, $search = '', $start = 0, $limit = 10, $orderfld = 'p.id_product', $orderdir = 'ASC', $id_shop = null)
        {
            $search_filtered = KIHelpers::filterSearchQuery($search, array('id', 'name'));

            if ($id_lang == null) {
                $id_lang = Context::getContext()->language->id;
            }

            $sql = new DbQuery();
            $sql->select('DISTINCT p.`id_product`');
            $sql->from('product', 'p');
            $sql->leftJoin('product_shop', 's', 'p.`id_product` = s.`id_product`');
            $sql->leftJoin('product_lang', 'l', 'p.`id_product` = l.`id_product`');

            if ($id_category != null) {
                $sql->leftJoin('category_product', 'cp', 'p.`id_product` = cp.`id_product`');
                $sql->where('cp.`id_category` = ' . (int)$id_category);
            }

            if ($search_filtered['id'] != '') {
                $sql->where('p.`id_product` IN (' . (int)$search_filtered['id'] . ')');
            }

            if ($search_filtered['name'] != '') {
                $sql->where('l.`name` LIKE \'%' . pSQL($search_filtered['name']) . '%\'');
            }

            if ($search_filtered['id'] == '' && $search_filtered['name'] == '' && $search_filtered['query'] != '') {
                $sql->where('l.`name` LIKE \'%' . pSQL($search_filtered['query']) . '%\'');
            }

            $sql->where('l.`id_lang` = ' . (int)$id_lang . ' AND s.`id_shop` = l.`id_shop`');

            if ($id_shop != null) {
                $sql->where('s.`id_shop` IN (' . pSQL($id_shop) . ')');
                $sql->where('l.`id_shop` IN (' . pSQL($id_shop) . ')');
            }

            if ($limit !== null && $start !== null) {
                $sql->limit((int)$limit, (int)$start);
            }

            if ($orderfld == 'entity_item_id') {
                $orderfld = 'p.id_product';
            }

            if ($orderfld == 'text') {
                $orderfld = 'l.name';
            }

            if ($orderfld != '' && $orderdir != '') {
                $sql->orderby(bqSQL($orderfld) . ' ' . bqSQL($orderdir));
            }

            return Db::getInstance()->executeS($sql);
        }

        public static function getNumProducts($id_category = null, $search = '', $id_shop = null)
        {
            $search_filtered = KIHelpers::filterSearchQuery($search, array('id', 'name'));

            $sql = new DbQuery();
            $sql->select('COUNT(DISTINCT p.`id_product`) AS total');
            $sql->from('product', 'p');
            $sql->leftJoin('product_shop', 's', 'p.`id_product` = s.`id_product`');
            $sql->leftJoin('product_lang', 'l', 'p.`id_product` = l.`id_product`');

            if ($id_category != null) {
                $sql->leftJoin('category_product', 'cp', 'p.`id_product` = cp.`id_product`');
                $sql->where('cp.`id_category` = ' . (int)$id_category);
            }

            if ($search_filtered['id'] != '') {
                $sql->where('p.`id_product` IN (' . (int)$search_filtered['id'] . ')');
            }

            if ($search_filtered['name'] != '') {
                $sql->where('l.`name` LIKE \'%' . pSQL($search_filtered['name']) . '%\'');
            }

            if ($search_filtered['id'] == '' && $search_filtered['name'] == '' && $search_filtered['query'] != '') {
                $sql->where('l.`name` LIKE \'%' . pSQL($search_filtered['query']) . '%\'');
            }

            $sql->where('s.`id_shop` = l.`id_shop`');

            if ($id_shop != null) {
                $sql->where('s.`id_shop` IN (' . pSQL($id_shop) . ')');
                $sql->where('l.`id_shop` IN (' . pSQL($id_shop) . ')');
            }

            return Db::getInstance()->getValue($sql);
        }
    }
}
