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
 * Class CategoryOption
 */
class CategoryOption extends ObjectModel
{
    public $id_category = 0;
    public $duty = 0;
    public $shipping = 0;
    public $width = -1;
    public $height = -1;
    public $depth = -1;
    public $show_widget = 0;

    public static $definition = array(
        'table'   => 'category_option',
        'primary' => 'id_category_option',
        'fields'  => array(
            'id_category' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            /* Purechoice Fields */
            'duty'        => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'),
            'shipping'    => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'),
            'width'       => array('type' => self::TYPE_FLOAT),
            'height'      => array('type' => self::TYPE_FLOAT),
            'depth'       => array('type' => self::TYPE_FLOAT),
            'show_widget' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt')
        )
    );

    public static function getCategoryOptionByCategory($id_category)
    {
        $sql = new DbQuery();
        $sql->select('id_category_option');
        $sql->from('category_option');
        $sql->where('id_category = ' . (int)$id_category);
        $id_category_option = Db::getInstance()->getValue($sql);

        if ($id_category_option === false) {
            return false;
        } else {
            return new CategoryOption($id_category_option);
        }
    }


}
