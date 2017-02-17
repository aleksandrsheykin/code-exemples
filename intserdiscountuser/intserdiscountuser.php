<?php
/**
* Oppa nihuya
* 
*/
if (!defined('_PS_VERSION_'))
  exit;
 
class IntserDiscountUser extends Module
{
	const TABLE_NAME 	= 'intser_customerdiscount';
	const TABLE_NAME_CD = 'intser_cartdiscount';
	
	public function __construct()
	{
		$this->name = 'intserdiscountuser';
		$this->tab = 'others';
		$this->version = '1.0.0';
		$this->author = 'Intser';
		$this->need_instance = 0;
		$this->controllers = array('mydiscounts');
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_); 
		$this->bootstrap = true;

		parent::__construct();
		$this->displayName = $this->l('Индивидуальные скидки');
		$this->description = $this->l('Добавляет в карточку покупателя поля со скидками по категорям товаров.');

		$this->confirmUninstall = $this->l('Вы уверены, что хотите удалить модуль?');

		if (!Configuration::get('MYMODULE_NAME'))      
			$this->warning = $this->l('No name provided');
	}
	
	public function install()
	{
		if (!parent::install()
			|| !$this->registerHook('adminCustomers') 
			|| !$this->registerHook('customerAccount')
			|| !$this->registerHook('displayNav')
			|| !$this->registerHook('DisplayHeader')
			|| !$this->registerHook('actionBeforeCartUpdateQty')
			|| !$this->registerHook('displayMyAccountBlock')
			)
			return false;
			
	
		$sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.self::TABLE_NAME.'';
		Db::getInstance()->execute($sql);
		
		$sql = 'CREATE TABLE '._DB_PREFIX_.self::TABLE_NAME.' (	id int(10) AUTO_INCREMENT,
																id_customer int(10) UNSIGNED NOT NULL, 
																id_category int(10) UNSIGNED NOT NULL, 
																discount int(2),
																PRIMARY KEY (id),
																INDEX(id_customer),
																INDEX(id_category),
														FOREIGN KEY (id_customer) REFERENCES ps_customer(id_customer) ON DELETE CASCADE,
														FOREIGN KEY (id_category) REFERENCES ps_category(id_category) ON DELETE CASCADE
														) ENGINE=INNODB;';
		Db::getInstance()->execute($sql);
		
		$sql = 'CREATE TABLE '._DB_PREFIX_.self::TABLE_NAME_CD.' (	id_cart 	int(10) UNSIGNED NOT NULL, 
																	id_category int(10) UNSIGNED NOT NULL, 
																	discount 	int(2),
																	INDEX(id_cart),
															PRIMARY KEY (id_cart, id_category),
															FOREIGN KEY (id_cart) REFERENCES ps_cart(id_cart) ON DELETE CASCADE
															) ENGINE=INNODB;';
		Db::getInstance()->execute($sql);		
		
		return true;
	}

	public function uninstall()
	{
		if (!parent::uninstall())
			return false;

		$sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.self::TABLE_NAME.';';
		Db::getInstance()->execute($sql);
		
		$sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.self::TABLE_NAME_CD.';';
		Db::getInstance()->execute($sql);		
		
		return true;
	}
	
	public function hookAdminCustomers($params) //Выводит категории в карточке клиента
	{
		//$id_customer = $params['cart']->id_customer;
		$id_customer = $params['id_customer'];
		
		if ((isset($_POST['insert_selectedcat'])) 	//Новая скидка
			&&(intval($_POST['insert_selectedcat'])>0) 
			&&(isset($_POST['insert_discountval']))
			&&(intval($_POST['insert_discountval'])>0)) 
		{
			$selectedCat = intval($_POST['insert_selectedcat']);
			$discountVal = intval($_POST['insert_discountval']);
			$this -> processInsertDiscount($id_customer, $selectedCat, $discountVal);
		}
		
		if ((isset($_POST['id_del'])) && (intval($_POST['id_del'])>0))	//удаление скидки
		{
			$id_del = intval($_POST['id_del']);
			$this -> processDeleteDiscount($id_customer, $id_del);			
		}

		$skidka = $this->GetArrayCategories();
		$this->smarty->assign(array(
			'skidka' => $skidka,
			'token' => Tools::getAdminToken('AdminCustomers'.(int)(Tab::getIdFromClassName('AdminCustomers')).(int)$this->context->employee->id)
		));
		$customer_list = $this->GetIntserCustomerDiscount($id_customer);
		$this->smarty->assign(array(
			'customer_list' => $customer_list,
		));		
		
		return $this->display(__FILE__, 'displaydiscounts.tpl');
	}
	
	public static function GetArrayCategories($id_lang = 2, $active = false, $sql_filter = '', $sql_sort = '', $sql_limit = '')
	{
		//$id_lang = $this->context->language->id;
	 	if (!Validate::isBool($active))
	 		die(Tools::displayError());
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.'category` c
			'.Shop::addSqlAssociation('category', 'c').'
			LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON c.`id_category` = cl.`id_category`'.Shop::addSqlRestrictionOnLang('cl').'
			WHERE 1 and c.id_parent = 2 '.$sql_filter.' '.($id_lang ? 'AND `id_lang` = '.(int)$id_lang : '').'
			'.($active ? 'AND `active` = 1' : '').'
			'.(!$id_lang ? 'GROUP BY c.id_category' : '').'
			'.($sql_sort != '' ? $sql_sort : 'ORDER BY cl.name').'
			'.($sql_limit != '' ? $sql_limit : '')
		);
  
		$categories = array();
		
		foreach ($result as $row) 
		{
			array_push($categories, 
				array(
					'cat_id'   => $row['id_category'],
					'cat_name' => $row['name'], 
				)			
			);
		}
		return $categories;
	}
		
	public function GetIntserCustomerDiscount($id_customer)
	{
		$categoriesCustomer = array();
		$sql = 'SELECT i.*, cl.name FROM '._DB_PREFIX_.self::TABLE_NAME.' i 
				LEFT JOIN '._DB_PREFIX_.'category_lang cl on cl.id_category = i.id_category
				WHERE i.id_customer = '.IntVal($id_customer).' AND cl.id_lang='.$this->context->language->id;
		if ($results = Db::getInstance()->ExecuteS($sql, true, false))
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
			return $categoriesCustomer;
		}
	}
	
	public function GetIntserCustomerDiscountWithCart($id_customer, $id_cart=0)
	{
		$categoriesCustomer = array();
		/*$sql = 'SELECT i.id_category, i.discount as discount_individual, cd.discount as discount_cart, cl.name FROM '._DB_PREFIX_.self::TABLE_NAME.' i 
				LEFT JOIN '._DB_PREFIX_.'category_lang cl on cl.id_category = i.id_category
				LEFT JOIN '._DB_PREFIX_.self::TABLE_NAME_CD.' cd on cd.id_category = i.id_category AND cd.id_cart='.$id_cart.'
				WHERE i.id_customer='.IntVal($id_customer).' AND cl.id_lang='.$this->context->language->id;*/
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
		//ddd($sql);		
		if ($results = Db::getInstance()->ExecuteS($sql, true, false))
		{
			foreach ($results as $row)
			{
				//ppp('discount_cart='.$row['discount_cart'].' discount_individual='.$row['discount_individual']);
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

	public function processInsertDiscount($id_customer, $id_category, $discount) 
	{
		$sql = 'SELECT * FROM '._DB_PREFIX_.self::TABLE_NAME.' WHERE 
													id_customer = '.$id_customer.' AND 
													id_category = '.$id_category;
		if (Db::getInstance()->getRow($sql)) 
		{
			$this->smarty->assign(array(
				'msg_err' => 'Эта категория уже добавлена',
			));
			return false;
		} else
		{
			Db::getInstance()->insert(self::TABLE_NAME, array(
				'id_customer' => (int)$id_customer,
				'id_category' => (int)$id_category,
				'discount'    => (int)$discount,
			));
			return true;
		}
	}	
	
	public function processDeleteDiscount($id_customer, $id_category)
	{
		Db::getInstance()->delete(_DB_PREFIX_.self::TABLE_NAME, 'id_category='.$id_category.' AND id_customer='.$id_customer);
		return true;
	}
	
	public function hookCustomerAccount($params)	//кнопка в аккаунте клиента
	{
		return $this->display(__FILE__, 'my-account.tpl');
	}

	public function hookDisplayMyAccountBlock($params)
	{
		//ppp('asdf');
		return $this->hookCustomerAccount($params);
	}	
	
	public function hookDisplayNav($params)		//"Мои скидки" в шапке сайта
	{
		if ($this->context->customer->isLogged()) 
		{
			if (isset($params['cart']->id) && $params['cart']->id)
			{
				$discount_list = $this->GetIntserCustomerDiscountWithCart($params['cart']->id_customer, $params['cart']->id);
				$this->smarty->assign(array(
					'discount_list' => $discount_list,
					'intser_countrow' => count($discount_list)?count($discount_list):1,
				));
				return $this->display(__FILE__, 'displaydiscountsheader.tpl');
			}
		}
	}
	
	public function hookDisplayHeader()
	{
		if ($this->context->customer->isLogged()) 
		{		
			$this->context->controller->addJS($this->_path.'js/intserjs.js');
			$this->context->controller->addCSS($this->_path.'css/intsercss.css');
		}
	}
	
	/*Ставим скидку в соответствии с правилами корзины*/
	public function hookActionBeforeCartUpdateQty($params)
	{
		$id_cart = $params['cart']->id;
		$operator = $params['operator'];
		$quantity_update_delta = $params['quantity'];
		$id_product_current = $params['product']->id;	
		
		$arr_categories = Category::getCategories();	//делаем массив с категориями пр. 4809 => 0, 4810 => 0,... будем хранить суммы 
		$arr_catsum = array();
		foreach ($arr_categories[2] as $key => $cat)	//[2] - пользовательские категории
			$arr_catsum[$cat['infos']['id_category']] = 0;		
		
		$products_incart = $params['cart']->getProducts();
		foreach ($products_incart as $key => $prod_incart)	//пробегаем по всей корзине
		{
			$quantity = $prod_incart['cart_quantity'];
			$price = Product::getPriceStatic($prod_incart['id_product'], true, null, 6, null, false, false);
			$sum_price = 0;
			$id_category = $prod_incart['id_category_default'];
			if ($id_product_current == $prod_incart['id_product'])
			{
				if ($operator == 'up') 
				{
					$quantity += $quantity_update_delta;
				} else { 
					if ($operator == 'down') 
					{
						$quantity -= $quantity_update_delta;
					}
				}
				$sum_price = $quantity * $price;
			} else {
				$sum_price = $quantity * $price;
			}
			$arr_catsum[$id_category] += $sum_price;	//массив содержит суммы цен без скидок по категориям
		}
		//file_put_contents('mylog.txt', implode(',', $arr_catsum));
		
		foreach ($arr_catsum as $id_category => $ruleSum)
		{
			$discount = $this->GetCartRulesCategory($id_category, $ruleSum);
			if ($discount > 0)
			{
				$sql = 'INSERT INTO '._DB_PREFIX_.self::TABLE_NAME_CD.' (id_cart, id_category, discount) 
						VALUES ('.$id_cart.', '.$id_category.', '.$discount.')
						ON DUPLICATE KEY UPDATE discount='.$discount.';';
				Db::getInstance()->execute($sql);
			} else {
				$sql = 'DELETE FROM '._DB_PREFIX_.self::TABLE_NAME_CD.'
						WHERE id_cart = '.$id_cart.' AND id_category = '.$id_category;
				Db::getInstance()->execute($sql);				
			}
		}		
		return true;
	}
	
	public function GetCartRulesCategory($id_category=0, $ruleSum=0)
	{
		$sql = 'SELECT r.reduction_percent
				  FROM '._DB_PREFIX_.'cart_rule_product_rule_value v
				  LEFT JOIN '._DB_PREFIX_.'cart_rule_product_rule_group g ON g.id_product_rule_group = v.id_product_rule
				  LEFT JOIN '._DB_PREFIX_.'cart_rule r ON r.id_cart_rule = g.id_cart_rule
				WHERE v.id_item = '.$id_category.' AND r.minimum_amount <= '.$ruleSum.'
				ORDER BY r.minimum_amount DESC';
		//file_put_contents('mylog.txt', $sql);
		return (int)Db::getInstance()->getValue($sql);
	}	
	
}	