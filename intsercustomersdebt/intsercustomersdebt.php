<?php
/**
* Oppa nihuya
* 
*/
if (!defined('_PS_VERSION_'))
  exit;
 
class IntserCustomersDebt extends Module
{
	const TABLE_NAME1 = 'intser_customerdebt';
	const TABLE_NAME2 = 'intser_customerdebtgrath';
	const TABLE_NAME3 = 'intser_customernotspread';
	
	public function __construct()
	{
		$this->name = 'intsercustomersdebt';
		$this->tab = 'others';
		$this->version = '1.0.0';
		$this->author = 'Intser';
		$this->need_instance = 0;
		$this->controllers = array('mydebts');
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_); 
		$this->bootstrap = true;

		parent::__construct();
		$this->displayName = $this->l('Блок финансовой информации');
		$this->description = $this->l('Добавляет блок информации о оплатах, задолженностях, заказах.');

		$this->confirmUninstall = $this->l('Вы уверены, что хотите удалить модуль?');

		if (!Configuration::get('MYMODULE_NAME'))      
			$this->warning = $this->l('No name provided');
	}
	
	public function install()
	{
		if (!parent::install()
			|| !$this->registerHook('customerAccount')
			|| !$this->registerHook('displayMyAccountBlock')
			)
			return false;
			
	
		$sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.self::TABLE_NAME1.'';
		Db::getInstance()->execute($sql);
		$sql = 'CREATE TABLE '._DB_PREFIX_.self::TABLE_NAME1.' (id int(10) AUTO_INCREMENT,
																id_customer int(10) UNSIGNED NOT NULL,
																ndoc varchar(32),
																date_doc date,
																sum_order decimal(20,6),
																sum_debt decimal(20,6),
																sum_overdue decimal(20,6),
																PRIMARY KEY (id),
																INDEX(id),
																INDEX(id_customer),
														FOREIGN KEY (id_customer) REFERENCES '._DB_PREFIX_.'customer(id_customer) ON DELETE CASCADE
														) ENGINE=INNODB;';
		Db::getInstance()->execute($sql);
		
		$sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.self::TABLE_NAME2.'';
		Db::getInstance()->execute($sql);
		$sql = 'CREATE TABLE '._DB_PREFIX_.self::TABLE_NAME2.' (id int(10) AUTO_INCREMENT,
																id_debt int(10),
																sum_debt_unit decimal(20,6),
																date_payment date,
																PRIMARY KEY (id),
																INDEX(id_debt),
														FOREIGN KEY (id_debt) REFERENCES '._DB_PREFIX_.self::TABLE_NAME1.'(id) ON DELETE CASCADE
														) ENGINE=INNODB;';
		Db::getInstance()->execute($sql);
		
		$sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.self::TABLE_NAME3.'';
		Db::getInstance()->execute($sql);		
		$sql = 'CREATE TABLE '._DB_PREFIX_.self::TABLE_NAME3.' (id int(10) AUTO_INCREMENT,
																id_customer int(10) UNSIGNED NOT NULL,
																ndoc varchar(32),
																sum_order decimal(20,6),
																sum_notspread decimal(20,6),
																date_payment date,
																PRIMARY KEY (id),
																INDEX(id),
																INDEX(id_customer),
														FOREIGN KEY (id_customer) REFERENCES '._DB_PREFIX_.'customer(id_customer) ON DELETE CASCADE
														) ENGINE=INNODB;';
		Db::getInstance()->execute($sql);		
		
		return true;
	}

	public function uninstall()
	{
		if (!parent::uninstall())
			return false;

		$sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.self::TABLE_NAME3.';';
		Db::getInstance()->execute($sql);		
		$sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.self::TABLE_NAME2.';';
		Db::getInstance()->execute($sql);
		$sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.self::TABLE_NAME1.';';
		Db::getInstance()->execute($sql);		
		
		return true;
	}
	
	public function hookCustomerAccount($params)	//кнопка в аккаунте клиента
	{
		return $this->display(__FILE__, 'my-account.tpl');
	}

	public function hookDisplayMyAccountBlock($params)
	{
		return $this->hookCustomerAccount($params);
	}	
	
}	