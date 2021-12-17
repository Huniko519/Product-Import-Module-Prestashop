<?php

class AdminPurechoiceReportController extends ModuleAdminController
{
    public function init()
    {
        parent::init();
        $this->bootstrap = true;
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->addJqueryUI('ui.datepicker');
    }

    public function initContent()
    {
        parent::initContent();

        $this->context->smarty->assign([
            'action_url'            => Dispatcher::getInstance()->createUrl('AdminPurechoiceReport', $this->context->language->id, array(), false),
            'controller'            => 'AdminPurechoiceReport',
            'token'                 => Tools::getAdminTokenLite('AdminPurechoiceReport'),
            'date_from'             => '01-' . (new DateTime())->format('m-Y'),
            'date_to'               => (new DateTime())->format('d-m-Y'),
            'order_states'          => OrderStateCore::getOrderStates($this->context->language->id),
            'order_states_selected' => array(2, 4, 5)
        ]);

        $this->setTemplate('report.tpl');
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

    public function taxReport()
    {
        /* parameters */
        $date_from = trim(Tools::getValue('date_from', ''));

        if ($date_from !== '' && !preg_match('/^\d{2}-\d{2}-\d{4}$/', $date_from)) {
            echo 'Invalid date from.';
            die();
        }

        $date_to = trim(Tools::getValue('date_to', ''));

        if ($date_to !== '' && !preg_match('/^\d{2}-\d{2}-\d{4}$/', $date_to)) {
            echo 'Invalid date to.';
            die();
        }

        $order_states = (array)Tools::getValue('order_states', '');
        $order_states = implode(',', $order_states);

        /* where condition */
        $where = '';
        $where_conditions = array();

        if ($date_from !== '' && $date_to !== '') {
            $date_from = DateTime::createFromFormat('d-m-Y', $date_from);
            $date_to = DateTime::createFromFormat('d-m-Y', $date_to);
            $where_conditions[] = 'o.date_add >= "' . $date_from->format('Y-m-d') . ' 00:00:00" and o.date_add <= "' . $date_to->format('Y-m-d') . ' 23:59:59"';
        }

        if ($order_states !== '') {
            $where_conditions[] = 'o.current_state in (' . implode(',', array_map('intval', explode(',', $order_states))) . ')';
        }

        if (count($where_conditions)) {
            $where = 'where ' . implode(' and ', $where_conditions);
        }

        /* for products */
        $sql_product = 'select odt.id_tax,
               DATE_FORMAT(o.date_add, "%d, %b %y") as order_date,
               o.id_order,
               sum(od.total_price_tax_excl) as product_total,
               sum(od.total_price_tax_incl - od.total_price_tax_excl) as product_tax_total
        from ' . _DB_PREFIX_ . 'orders o
        left join ' . _DB_PREFIX_ . 'order_detail od on od.id_order = o.id_order
        left join ' . _DB_PREFIX_ . 'order_detail_tax odt on odt.id_order_detail = od.id_order_detail
        ' . $where . '
        group by odt.id_tax, o.id_order
        order by o.id_order desc';
        $products = Db::getInstance()->executeS($sql_product);
        $orders = array_unique(array_column($products, 'id_order'));
        $products_temp = array();

        foreach ($products as $product) {
            $id_tax = is_null($product['id_tax']) ? 0 : $product['id_tax'];
            $product['id_tax'] = $id_tax;

            if (!isset($products_temp[$id_tax])) {
                $products_temp[$id_tax] = array();
            }

            $products_temp[$id_tax][] = $product;
        }

        $products = $products_temp;

        /* for shipping */
        $sql_shipping = 'select t.id_tax,
                   DATE_FORMAT(o.date_add, "%d, %b %y") as order_date,
                   o.id_order,
                   sum(o.total_shipping_tax_excl) as shipping_total,
                   sum(oc.shipping_cost_tax_incl - oc.shipping_cost_tax_excl) as shipping_tax_total
            from ' . _DB_PREFIX_ . 'orders o
            left join ' . _DB_PREFIX_ . 'order_carrier oc on oc.id_order = o.id_order
            left join ' . _DB_PREFIX_ . 'carrier_tax_rules_group_shop ct on ct.id_carrier = o.id_carrier
            left join ' . _DB_PREFIX_ . 'address a on a.id_address = o.id_address_delivery
            left join ' . _DB_PREFIX_ . 'tax_rule tr on tr.id_tax_rules_group = ct.id_tax_rules_group and tr.id_country = a.id_country and tr.id_state = a.id_state
            left join ' . _DB_PREFIX_ . 'tax t on t.id_tax = tr.id_tax and t.deleted = 0 and t.active = 1
            ' . $where . '
            group by t.id_tax, o.id_order
            order by o.id_order desc';
        $shippings = Db::getInstance()->executeS($sql_shipping);
        $orders = array_unique(array_merge($orders, array_unique(array_column($shippings, 'id_order'))));
        $shippings_temp = array();

        foreach ($shippings as $shipping) {
            $id_tax = is_null($shipping['id_tax']) ? 0 : $shipping['id_tax'];
            $shipping['id_tax'] = $id_tax;

            if (!isset($shippings_temp[$shipping['id_tax']])) {
                $shippings_temp[$shipping['id_tax']] = array();
            }

            $shippings_temp[$shipping['id_tax']][] = $shipping;
        }

        $shippings = $shippings_temp;

        /* for tax */
        $sql_tax = 'select t.id_tax,
                   t.rate,
                   tl.name
            from ' . _DB_PREFIX_ . 'tax t
            left join ' . _DB_PREFIX_ . 'tax_lang tl on tl.id_tax = t.id_tax and tl.id_lang = ' . $this->context->language->id . '
            where t.deleted = 0 and t.active = 1';
        $taxes = Db::getInstance()->executeS($sql_tax);
        $taxes[] = array('id_tax' => 0, 'name' => 'None');

        /* prepare csv data */
        $date_from = is_string($date_from) ? '--' : $date_from->format('d, M y');
        $date_to = is_string($date_to) ? '--' : $date_to->format('d, M y');

        $csv_data = array();
        $csv_data[] = array('From', 'To', '', '', '', '', '');
        $csv_data[] = array($date_from, $date_to, '', '', '', '', '');
        $csv_data[] = array_fill(0, 7, '');
        $csv_data[] = array('Date', 'Order #', 'Products', 'Product Taxes', 'Shipping', 'Shipping Taxes', 'Total');
        $csv_data[] = array_fill(0, 7, '');

        $all_products_total = 0;
        $all_products_tax_total = 0;
        $all_shippings_total = 0;
        $all_shippings_tax_total = 0;
        $all_grand_total = 0;

        foreach ($taxes as $tax) {
            $id_tax = $tax['id_tax'];
            $rate = $tax['rate'];

            if ((!isset($products[$id_tax]) || !count($products[$id_tax])) && (!isset($shippings[$id_tax]) || !count($shippings[$id_tax]))) {
                continue;
            }

            $csv_data[] = array('Province:', $tax['name'] . ' (ID: ' . $id_tax . ', Rate: ' . ($rate + 0) . '%)', '', '', '', '', '');
            $csv_data[] = array_fill(0, 7, '');

            $products_total = 0;
            $products_tax_total = 0;
            $shippings_total = 0;
            $shippings_tax_total = 0;
            $grand_total = 0;

            foreach ($orders as $order) {
                $id_order = $order;

                $product_by_order = null;

                if (isset($products[$id_tax])) {
                    $product_by_order = array_filter($products[$id_tax], function ($product) use ($id_order) { return $product['id_order'] === $id_order; });
                    $product_by_order = count($product_by_order) ? reset($product_by_order) : null;
                }

                $shipping_by_order = null;

                if (isset($shippings[$id_tax])) {
                    $shipping_by_order = array_filter($shippings[$id_tax], function ($shipping) use ($id_order) { return $shipping['id_order'] === $id_order; });
                    $shipping_by_order = count($shipping_by_order) ? reset($shipping_by_order) : null;
                }

                if ($product_by_order === null && $shipping_by_order === null) {
                    continue;
                }

                $product_total = ($product_by_order !== null) ? $product_by_order['product_total'] : 0;
                $product_tax_total = ($product_by_order !== null) ? $product_by_order['product_tax_total'] : 0;
                $shipping_total = ($shipping_by_order !== null) ? $shipping_by_order['shipping_total'] : 0;
                $shipping_tax_total = ($shipping_by_order !== null) ? $shipping_by_order['shipping_tax_total'] : 0;
                $sub_total = $product_total + $product_tax_total + $shipping_total + $shipping_tax_total;

                $order_date = ($product_by_order !== null) ? $product_by_order['order_date'] : $shipping_by_order['order_date'];
                $csv_data[] = array($order_date, '#' . $id_order, $product_total, $product_tax_total, $shipping_total, $shipping_tax_total, $sub_total);

                $products_total += $product_total;
                $products_tax_total += $product_tax_total;
                $shippings_total += $shipping_total;
                $shippings_tax_total += $shipping_tax_total;
                $grand_total += $sub_total;
            }

            $csv_data[] = array_fill(0, 7, '');
            $csv_data[] = array('', 'Total:', $products_total, $products_tax_total, $shippings_total, $shippings_tax_total, $grand_total);
            $csv_data[] = array_fill(0, 7, '');

            $all_products_total += $products_total;
            $all_products_tax_total += $products_tax_total;
            $all_shippings_total += $shippings_total;
            $all_shippings_tax_total += $shippings_tax_total;
            $all_grand_total += $grand_total;
        }

        $csv_data[] = array('', '', '', '', '', 'Total Products:', $all_products_total);
        $csv_data[] = array('', '', '', '', '', 'Total Products Tax:', $all_products_tax_total);
        $csv_data[] = array('', '', '', '', '', 'Total Shipping:', $all_shippings_total);
        $csv_data[] = array('', '', '', '', '', 'Total Shipping Tax:', $all_shippings_tax_total);
        $csv_data[] = array('', '', '', '', '', 'Subtotal:', $all_products_total + $all_shippings_total);
        $csv_data[] = array('', '', '', '', '', 'Total Tax:', $all_products_tax_total + $all_shippings_tax_total);
        $csv_data[] = array('', '', '', '', '', 'Total Sales:', $all_grand_total);

        header('Content-type: text/csv');
        header('Content-Type: application/force-download; charset=UTF-8');
        header('Cache-Control: no-store, no-cache');
        header('Content-disposition: attachment; filename="export.csv"');

        $csv = fopen("php://output", 'w');

        foreach ($csv_data as $row) {
            fputcsv($csv, $row, ',');
        }

        fclose($csv);
    }
}
