<?php

/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* IntserViewController
*/

//Отображает задолженности по ссылке "Мои долги" в личном кабинете

class IntserCustomersDebtMyDebtsModuleFrontController extends ModuleFrontController
{
	const TABLE_NAME1 = 'intser_customerdebt';
	const TABLE_NAME2 = 'intser_customerdebtgrath';
	const TABLE_NAME3 = 'intser_customernotspread';

	public function __construct()
	{
		parent::__construct();
		$this->context = Context::getContext();
	}

	public function initContent()
	{
		$this->page_name = 'Мои долги';
		$this->display_column_left = false;
		parent::initContent();
		
		//$this->context->controller->addCSS('css/intsercss.css');
		//$this->addCSS($this->_path.'css/intsercss.css'); 
		
		$debt_list = $this->GetDebts($this->context->customer->id);
		//ppp($debt_list);
		
		$payment_list = array();
		foreach ($debt_list as $key => $debt_row) {
			$payment_list[$debt_row['id']] = $this->GetPaymentGrath($debt_row['id']);
		}
		//ppp($payment_list);
		
		$notspread_list = $this->GetNotSpread($this->context->customer->id);
		//ppp($notspread_list);
		$this->context->smarty->assign(array(
			'debt_list' => $debt_list,
			'payment_list' => $payment_list,
			'notspread_list' => $notspread_list
		));			
		$this->setTemplate('mydebts.tpl');
	}
	
	public function GetDebts($id_customer=0)
	{
		$sql = "SELECT id, id_customer, ndoc, sum_order, sum_debt, sum_overdue, DATE_FORMAT(date_doc, '%d.%m.%Y') as date_doc FROM "._DB_PREFIX_.self::TABLE_NAME1." WHERE id_customer=".(int)$id_customer." ORDER BY id";
		return Db::getInstance()->executeS($sql);
	}
	
	public function GetPaymentGrath($id_debt=0)
	{
		$sql = "SELECT id, id_debt, sum_debt_unit, DATE_FORMAT(date_payment, '%d.%m.%Y') as date_payment FROM "._DB_PREFIX_.self::TABLE_NAME2." WHERE id_debt=".(int)$id_debt." ORDER BY date_payment";
		return Db::getInstance()->executeS($sql);
	}
	
	public function GetNotSpread($id_customer=0)
	{
		$sql = "SELECT id, id_customer, ndoc, sum_order, sum_notspread, DATE_FORMAT(date_payment, '%d.%m.%Y') as date_payment FROM "._DB_PREFIX_.self::TABLE_NAME3." WHERE id_customer=".(int)$id_customer." ORDER BY date_payment";
		return Db::getInstance()->executeS($sql);
	}	
	
}
