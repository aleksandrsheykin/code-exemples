<?php
/**
* Oppa nihuya
* 
*/
if (!defined('_PS_VERSION_'))
  exit;
 
class IntserAlertIntake extends Module
{
	const TABLE_NAME = 'intser_customerintake';
	
	public function __construct()
	{
		$this->name = 'intseralertintake';
		$this->tab = 'others';
		$this->version = '1.0.0';
		$this->author = 'Intser';
		$this->need_instance = 0;
		$this->controllers = array('customerintake');
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_); 
		$this->bootstrap = true;

		parent::__construct();
		$this->displayName = $this->l("Кнопка ''Оповестить при поступлении''");
		$this->description = $this->l('Добавляет кнупку "оповестить при поступлении", записывает товары в отдельную таблицу, в личном кабинете кнопка со списком товаров.');

		$this->confirmUninstall = $this->l('Вы уверены, что хотите удалить модуль?');

		if (!Configuration::get('MYMODULE_NAME'))      
			$this->warning = $this->l('No name provided');
	}
	
	public function install()
	{
		if (!parent::install()
			|| !$this->registerHook('customerAccount')
			|| !$this->registerHook('productActions') 
			|| !$this->registerHook('header') 
			)
			return false;
			
		$sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.self::TABLE_NAME.'';
		Db::getInstance()->execute($sql);
		$sql = 'CREATE TABLE '._DB_PREFIX_.self::TABLE_NAME.' ( id int(10) AUTO_INCREMENT,
																id_customer int(10) UNSIGNED NOT NULL,
																id_product int(10) UNSIGNED NOT NULL, 
																date_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
																PRIMARY KEY (id),
																INDEX(id),
																INDEX(id_customer),
																INDEX(id_product),
														FOREIGN KEY (id_customer) REFERENCES '._DB_PREFIX_.'customer(id_customer) ON DELETE CASCADE,
														FOREIGN KEY (id_product) REFERENCES '._DB_PREFIX_.'product(id_product) ON DELETE CASCADE
														) ENGINE=INNODB;';
		Db::getInstance()->execute($sql);
		
		return true;
	}

	public function uninstall()
	{
		if (!parent::uninstall())
			return false;
		
		$sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.self::TABLE_NAME.'';
		Db::getInstance()->execute($sql);
		
		return true;
	}
	
	public function hookCustomerAccount($params)	//кнопка в аккаунте клиента
	{
		return $this->display(__FILE__, 'myaccauntbtn.tpl');
	}
	
	public function hookProductActions($params)
	{
		$this->smarty->assign(array(
			'id_product' => (int)Tools::getValue('id_product'),
		));
		return $this->display(__FILE__, 'btnonpageproduct.tpl');
	}
	
	public function hookHeader($params)
	{
		$this->context->controller->addJS(($this->_path).'js/ajax-addproduct.js');
	}	
	

}	