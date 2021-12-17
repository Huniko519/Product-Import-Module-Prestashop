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

if (!class_exists('KICMSPages')) {
    /**
     * Class KICMSPages
     */
    class KICMSPages
    {
        public static function getCMSPages($id_lang = null, $search = '', $start = null, $limit = null, $orderfld = 'c.id_cms', $orderdir = 'ASC', $id_shop = null)
        {
            $search_filtered = KIHelpers::filterSearchQuery($search, array('id', 'text'));

            if ($id_lang == null) {
                $id_lang = Context::getContext()->language->id;
            }

            $sql = new DbQuery();
            $sql->select('c.`id_cms`, l.`meta_title`');
            $sql->from('cms', 'c');
            $sql->leftJoin('cms_lang', 'l', 'c.`id_cms` = l.`id_cms`');
            $sql->leftJoin('cms_shop', 's', 'c.`id_cms` = s.`id_cms`');

            if ($search_filtered['id'] != '') {
                $sql->where('c.`id_cms` IN (' . (int)$search_filtered['id'] . ')');
            }

            if ($search_filtered['text'] != '') {
                $sql->where('l.`meta_title` LIKE \'%' . pSQL($search_filtered['text']) . '%\'');
            }

            if ($search_filtered['id'] == '' && $search_filtered['text'] == '' && $search_filtered['query'] != '') {
                $sql->where('l.`meta_title` LIKE \'%' . pSQL($search_filtered['query']) . '%\'');
            }

            $sql->where('l.`id_lang` = ' . (int)$id_lang . ' AND s.`id_shop` = l.`id_shop`');

            if ($id_shop != null) {
                $sql->where('s.`id_shop` IN (' . pSQL($id_shop) . ')');
                $sql->where('l.`id_shop` IN (' . pSQL($id_shop) . ')');
            }

            $sql->where('c.`active` = 1');

            if ($limit !== null && $start !== null) {
                $sql->limit((int)$limit, (int)$start);
            }

            if ($orderfld == 'entity_item_id') {
                $orderfld = 'c.id_cms';
            }

            if ($orderfld == 'text') {
                $orderfld = 'l.meta_title';
            }

            if ($orderfld != '' && $orderdir != '') {
                $sql->orderby(bqSQL($orderfld) . ' ' . bqSQL($orderdir));
            }

            return Db::getInstance()->executeS($sql);
        }

        public static function getNumCMSPages($search = '', $id_shop = null)
        {
            $search_filtered = KIHelpers::filterSearchQuery($search, array('id', 'text'));

            $sql = new DbQuery();
            $sql->select('COUNT(DISTINCT c.`id_cms`) AS total');
            $sql->from('cms', 'c');
            $sql->leftJoin('cms_lang', 'l', 'c.`id_cms` = l.`id_cms`');
            $sql->leftJoin('cms_shop', 's', 'c.`id_cms` = s.`id_cms`');

            if ($search_filtered['id'] != '') {
                $sql->where('c.`id_cms` IN (' . (int)$search_filtered['id'] . ')');
            }

            if ($search_filtered['text'] != '') {
                $sql->where('l.`meta_title` LIKE \'%' . pSQL($search_filtered['text']) . '%\'');
            }

            if ($search_filtered['id'] == '' && $search_filtered['text'] == '' && $search_filtered['query'] != '') {
                $sql->where('l.`meta_title` LIKE \'%' . pSQL($search_filtered['query']) . '%\'');
            }

            $sql->where('s.`id_shop` = l.`id_shop`');

            if ($id_shop != null) {
                $sql->where('s.`id_shop` IN (' . pSQL($id_shop) . ')');
                $sql->where('l.`id_shop` IN (' . pSQL($id_shop) . ')');
            }

            $sql->where('c.`active` = 1');

            return Db::getInstance()->getValue($sql);
        }
    }
}
