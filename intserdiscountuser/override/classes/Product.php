<?php
/*
* IntserDiscountUser
*/
class Product extends ProductCore
{
	const TABLE_NAME = 'intser_customerdiscount';
	const TABLE_NAME_CD = 'intser_cartdiscount';	
	
	public static function getPriceStatic($id_product, $usetax = true, $id_product_attribute = null, $decimals = 6, $divisor = null,
		$only_reduc = false, $usereduc = true, $quantity = 1, $force_associated_tax = false, $id_customer = null, $id_cart = null,
		$id_address = null, &$specific_price_output = null, $with_ecotax = true, $use_group_reduction = true, Context $context = null,
		$use_customer_price = true)
	{
		$a = parent::getPriceStatic($id_product, $usetax, $id_product_attribute, $decimals, $divisor,
			$only_reduc, $usereduc, $quantity, $force_associated_tax, $id_customer, $id_cart, $id_address, 
			$specific_price_output, $with_ecotax, $use_group_reduction, $context, $use_customer_price);

		if (isset(Context::getContext()->customer->id))
			$id_cust = intval(Context::getContext()->customer->id);
		else
			$id_cust = 0;
		
		if ($id_cust) 
		{	
			$id_category = Product::getProductCategories($id_product)[0];
			$id_cart = Context::getContext()->cart->id; 
			$discount = Product::GetDiscountPercent($id_cust, $id_category, $id_cart);
			if ($discount) $specific_price_output = array('reduction' => $discount/100, 
														'reduction_type' => 'percentage', 
														'from_quantity' => 1, 
														'id_currency' => Context::getContext()->currency, 
														'price' => 0, 
														'from' => 0, 
														'to' => 0);
			if ($usereduc)
			{
				$a = Product::GetPriceWithDiscount($discount, $a);
			} 
		}
		return $a;
	}

	public static function GetDiscountPercent($id_customer, $id_category, $id_cart=0)
	{
		$sql = 'SELECT discount FROM '._DB_PREFIX_.self::TABLE_NAME.' WHERE id_customer='.(int)$id_customer.' AND id_category='.(int)$id_category;
		$d_individual = intval(Db::getInstance()->getValue($sql));
		$d_general = 0;
		if ($id_cart) 
		{
			$sql = 'SELECT discount FROM '._DB_PREFIX_.self::TABLE_NAME_CD.' WHERE id_cart='.(int)$id_cart.' AND id_category='.(int)$id_category;
			$d_general = intval(Db::getInstance()->getValue($sql));
		}
		return $d_individual>$d_general?$d_individual:$d_general;
	}
	
	public static function GetPriceWithDiscount($percent, $price)
	{
		if ($percent >= 100)
			return $price;
		
		return (float)$price-$price*$percent/100;
	}	
}
