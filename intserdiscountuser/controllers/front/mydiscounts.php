<?php

/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* IntserViewController
*/

//Отображает таблицу индивидуальных скидок в личном кабинете клиента

class IntserDiscountUserMyDiscountsModuleFrontController extends ModuleFrontController
{	

	const TABLE_NAME = 'intser_customerdiscount';
	const TABLE_NAME_CD = 'intser_cartdiscount';	

	public function __construct()
	{
		parent::__construct();
		$this->context = Context::getContext();
	}

	public function initContent()
	{
		$this->page_name = 'Мои скидки';
		$this->display_column_left = false;
		parent::initContent();
		
		
		//$discount_list = $this->GetDiscounts($this->context->customer->id);
		$discount_list = $this->GetIntserCustomerDiscountWithCart($this->context->customer->id, $this->context->cart->id);
		
		$this->context->smarty->assign(array(
			'discount_list' => $discount_list,
		));	
		
		$this->setTemplate('mydiscounts.tpl');
	}
	
	public function GetDiscounts($id_customer) //берет индивидуальные скидки клиента
	{
		$categoriesCustomer = array();
		$sql = 'SELECT i.*, cl.name FROM '._DB_PREFIX_.self::TABLE_NAME.' i 
				LEFT JOIN '._DB_PREFIX_.'category_lang cl on cl.id_category = i.id_category
				WHERE i.id_customer = '.IntVal($id_customer).' AND cl.id_lang=2 ';
		if ($results = Db::getInstance()->ExecuteS($sql))
		{
			foreach ($results as $row)
			{
				array_push($categoriesCustomer, 
					array(
						'cat_id'   => $row['id_category'],
						'cat_name' => $row['name'],
						'discount' => $row['discount'],
					)			
				);
			}
			//ppp($categoriesCustomer);
			return $categoriesCustomer;
		}
	}
	
	public function GetIntserCustomerDiscountWithCart($id_customer, $id_cart=0)	//убрать
	{
		$categoriesCustomer = array();

		$sql = 'SELECT SUM(f.discount_individual) AS discount_individual, SUM(f.discount_cart) AS discount_cart, f.id_category, cl.name
				FROM 
				  (
					SELECT i.discount AS discount_individual, 0 AS discount_cart, i.id_category
					FROM '._DB_PREFIX_.self::TABLE_NAME.' i 
					WHERE i.id_customer = '.$id_customer.'
					UNION
					SELECT 0 AS discount_individual, cd.discount AS discount_cart, cd.id_category
					FROM '._DB_PREFIX_.self::TABLE_NAME_CD.' cd 
					WHERE cd.id_cart = '.$id_cart.'
				  ) f
				LEFT JOIN '._DB_PREFIX_.'category_lang cl ON cl.id_category = f.id_category AND cl.id_lang = '.$this->context->language->id.'
				GROUP BY f.id_category, cl.name';
		
		if ($results = Db::getInstance()->ExecuteS($sql, true, false))
		{
			foreach ($results as $row)
			{
				array_push($categoriesCustomer, 
					array(
						'cat_id'   => $row['id_category'],
						'cat_name' => $row['name'],
						'discount_individual' => $row['discount_individual'],
						'discount_cart' => $row['discount_cart'],
					)			
				);
			}
			return $categoriesCustomer;
		}
	}		
	
}
