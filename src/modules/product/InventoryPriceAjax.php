<?php
	global $theme, $log;
	$theme_path='themes/'.$theme.'/';
	$image_path=$theme_path.'images/';

	$currencyid = $_REQUEST['currencyid'];
	$products_list = $_REQUEST['productsList'];

	$product_ids = explode('::', $products_list);

	$price_list = array();
	$prices_for_products = array();
	$productNumbers = count($product_ids);

	if ($productNumbers > 0) {
		$prices_for_products = getPricesForProducts($currencyid, $product_ids, 'product');
		$prices_for_services = getPricesForProducts($currencyid, $product_ids, 'Services');
	}

	// To get the Price Values in the same order as the Products
	for ($i = 0; $i < $productNumbers; $i++) {
		$product_id = $product_ids[$i];
		// Pick the price for the product_id from service prices/ product prices based on which array it is set.
		$price_list[] = empty($prices_for_services[$product_id]) ? $prices_for_products[$product_id] : $prices_for_services[$product_id];
	}

	$price_values = implode('::', $price_list);
	echo 'SUCCESS$'.$price_values;
