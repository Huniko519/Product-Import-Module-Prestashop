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

if (!class_exists('KICustomers')) {
    /**
     * Class KICustomers
     */
    class KICustomers
    {
        public static function getCustomers($search = '', $start = 0, $limit = 10, $orderfld = 'c.id_customer', $orderdir = 'ASC', $id_shop = null)
        {
            $search_filtered = KIHelpers::filterSearchQuery($search, array('id', 'firstname', 'lastname', 'email'));

            $sql = new DbQuery();
            $sql->select('c.`id_customer`, c.`firstname`, c.`lastname`, c.`email`');
            $sql->from('customer', 'c');

            if ($search_filtered['id'] != '') {
                $sql->where('c.`id_customer` IN (' . (int)$search_filtered['id'] . ')');
            }

            if ($search_filtered['firstname'] != '') {
                $sql->where('c.`firstname` LIKE \'%' . pSQL($search_filtered['firstname']) . '%\'');
            }

            if ($search_filtered['lastname'] != '') {
                $sql->where('c.`lastname` LIKE \'%' . pSQL($search_filtered['lastname']) . '%\'');
            }

            if ($search_filtered['email'] != '') {
                $sql->where('c.`email` LIKE \'%' . pSQL($search_filtered['email']) . '%\'');
            }

            if ($search_filtered['id'] == '' && $search_filtered['firstname'] == ''
                && $search_filtered['lastname'] == ''
                && $search_filtered['email'] == ''
                && $search_filtered['query'] != ''
            ) {
                $sql->where('c.`firstname` LIKE \'%' . pSQL($search_filtered['query']) . '%\' OR
				c.`lastname` LIKE \'%' . pSQL($search_filtered['query']) . '%\'');
            }

            if ($id_shop != null) {
                $sql->where('c.`id_shop` IN (' . pSQL($id_shop) . ')');
            }

            $sql->limit((int)$limit, (int)$start);
            $sql->orderby(bqSQL($orderfld) . ' ' . bqSQL($orderdir));

            return Db::getInstance()->executeS($sql);
        }

        public static function getNumCustomers($search = '', $id_shop = null)
        {
            $search_filtered = KIHelpers::filterSearchQuery($search, array('id', 'firstname', 'lastname', 'email'));

            $sql = new DbQuery();
            $sql->select('COUNT(DISTINCT c.`id_customer`) AS total');
            $sql->from('customer', 'c');

            if ($search_filtered['id'] != '') {
                $sql->where('c.`id_customer` IN (' . (int)$search_filtered['id'] . ')');
            }

            if ($search_filtered['firstname'] != '') {
                $sql->where('c.`firstname` LIKE \'%' . pSQL($search_filtered['firstname']) . '%\'');
            }

            if ($search_filtered['lastname'] != '') {
                $sql->where('c.`lastname` LIKE \'%' . pSQL($search_filtered['lastname']) . '%\'');
            }

            if ($search_filtered['email'] != '') {
                $sql->where('c.`email` LIKE \'%' . pSQL($search_filtered['email']) . '%\'');
            }

            if ($search_filtered['id'] == '' && $search_filtered['firstname'] == ''
                && $search_filtered['lastname'] == ''
                && $search_filtered['email'] == ''
                && $search_filtered['query'] != ''
            ) {
                $sql->where('c.`firstname` LIKE \'%' . pSQL($search_filtered['query']) . '%\' OR
				c.`lastname` LIKE \'%' . pSQL($search_filtered['query']) . '%\'');
            }

            if ($id_shop != null) {
                $sql->where('c.`id_shop` IN (' . pSQL($id_shop) . ')');
            }

            return Db::getInstance()->getValue($sql);
        }
    }
}
