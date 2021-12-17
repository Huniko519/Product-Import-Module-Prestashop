<?php
/**
 * Overrides carrier shipping with Table Rate Shipping
 *
 * Table Rate Shipping by Kahanit(https://www.kahanit.com/) is licensed under a
 * Creative Creative Commons Attribution-NoDerivatives 4.0 International License.
 * Based on a work at https://www.kahanit.com/.
 * Permissions beyond the scope of this license may be available at https://www.kahanit.com/.
 * To view a copy of this license, visit http://creativecommons.org/licenses/by-nd/4.0/.
 *
 * @author    Amit Sidhpura <amit@kahanit.com>
 * @copyright 2016 Kahanit
 * @license   http://creativecommons.org/licenses/by-nd/4.0/
 * @version   1.0.4.0
 */

/**
 * Class ProductOption
 */
class ProductOption extends ObjectModel
{
    public $id_product = 0;
    public $upc = '';
    public $caliber = '';
    public $velocity = '';
    public $image_url = '';
    public $wnet = 0;
    public $shipping = 0;
    public $exchange = 0;
    public $gst = 0;
    public $duty = 0;
    public $markup = 0;

    public static $definition = array(
        'table'   => 'product_option',
        'primary' => 'id_product_option',
        'fields'  => array(
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            /* Purechoice Fields */
            'upc'        => array('type' => self::TYPE_STRING),
            'caliber'    => array('type' => self::TYPE_STRING),
            'velocity'   => array('type' => self::TYPE_STRING),
            'image_url'  => array('type' => self::TYPE_STRING),
            'wnet'       => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'),
            'shipping'   => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'),
            'exchange'   => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'),
            'gst'        => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'),
            'duty'       => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'),
            'markup'     => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat')
        )
    );

    public static function getProductOptionByProduct($id_product)
    {
        $sql = new DbQuery();
        $sql->select('id_product_option');
        $sql->from('product_option');
        $sql->where('id_product = ' . (int)$id_product);
        $id_product_option = Db::getInstance()->getValue($sql);

        if ($id_product_option === false) {
            return false;
        } else {
            return new ProductOption($id_product_option);
        }
    }

    public static function getAll()
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('product_option');

        return Db::getInstance()->executeS($sql);
    }

    public static function getAllDistinctCaliberValues()
    {
        $sql = new DbQuery();
        $sql->select('DISTINCT `caliber`');
        $sql->from('product_option');

        $values = Db::getInstance()->executeS($sql);
        $values = array_map(function ($value) {
            return $value['caliber'];
        }, $values);
        sort($values);

        return $values;
    }

    public static function getAllDistinctVelocityValues()
    {
        $sql = new DbQuery();
        $sql->select('DISTINCT `velocity`');
        $sql->from('product_option');

        $values = Db::getInstance()->executeS($sql);
        $values = array_map(function ($value) {
            return $value['velocity'];
        }, $values);
        sort($values);

        return $values;
    }
}
