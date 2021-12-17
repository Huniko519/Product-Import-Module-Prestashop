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

if (!class_exists('KISuppliers')) {
    /**
     * Class KISuppliers
     */
    class KISuppliers
    {
        public static function getSuppliers($search = '', $start = null, $limit = null, $orderfld = 'm.id_supplier', $orderdir = 'ASC', $id_shop = null)
        {
            $search_filtered = KIHelpers::filterSearchQuery($search, array('id', 'text'));

            $sql = new DbQuery();
            $sql->select('m.`id_supplier`, m.`name`');
            $sql->from('supplier', 'm');
            $sql->leftJoin('supplier_shop', 's', 'm.`id_supplier` = s.`id_supplier`');

            if ($search_filtered['id'] != '') {
                $sql->where('m.`id_supplier` IN (' . (int)$search_filtered['id'] . ')');
            }

            if ($search_filtered['text'] != '') {
                $sql->where('m.`name` LIKE \'%' . pSQL($search_filtered['text']) . '%\'');
            }

            if ($search_filtered['id'] == '' && $search_filtered['text'] == '' && $search_filtered['query'] != '') {
                $sql->where('m.`name` LIKE \'%' . pSQL($search_filtered['query']) . '%\'');
            }

            if ($id_shop != null) {
                $sql->where('s.`id_shop` IN (' . pSQL($id_shop) . ')');
            }

            $sql->where('m.`active` = 1');

            if ($limit !== null && $start !== null) {
                $sql->limit((int)$limit, (int)$start);
            }

            if ($orderfld == 'entity_item_id') {
                $orderfld = 'm.id_supplier';
            }

            if ($orderfld == 'text') {
                $orderfld = 'm.name';
            }

            if ($orderfld != '' && $orderdir != '') {
                $sql->orderby(bqSQL($orderfld) . ' ' . bqSQL($orderdir));
            }

            return Db::getInstance()->executeS($sql);
        }

        public static function getNumSuppliers($search = '', $id_shop = null)
        {
            $search_filtered = KIHelpers::filterSearchQuery($search, array('id', 'text'));

            $sql = new DbQuery();
            $sql->select('COUNT(DISTINCT m.`id_supplier`) AS total');
            $sql->from('supplier', 'm');
            $sql->leftJoin('supplier_shop', 's', 'm.`id_supplier` = s.`id_supplier`');

            if ($search_filtered['id'] != '') {
                $sql->where('m.`id_supplier` IN (' . (int)$search_filtered['id'] . ')');
            }

            if ($search_filtered['text'] != '') {
                $sql->where('m.`name` LIKE \'%' . pSQL($search_filtered['text']) . '%\'');
            }

            if ($search_filtered['id'] == '' && $search_filtered['text'] == '' && $search_filtered['query'] != '') {
                $sql->where('m.`name` LIKE \'%' . pSQL($search_filtered['query']) . '%\'');
            }

            if ($id_shop != null) {
                $sql->where('s.`id_shop` IN (' . pSQL($id_shop) . ')');
            }

            $sql->where('m.`active` = 1');

            return Db::getInstance()->getValue($sql);
        }

        public static function getSupplierName($id = 0)
        {
            if ($id == 0) {
                return false;
            }

            $sql = new DbQuery();
            $sql->select('name');
            $sql->from('supplier');
            $sql->where('id_supplier = ' . (int)$id);

            $result = Db::getInstance()->getRow($sql);

            return $result['name'];
        }
    }
}
