<?php

/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* IntserViewController
*/

//Отображает задолженности по ссылке "Мои долги" в личном кабинете

class IntserAlertInTakeCustomerIntakeModuleFrontController extends ModuleFrontController
{
	const TABLE_NAME = 'intser_customerintake';

	public function __construct()
	{
		parent::__construct();
		$this->context = Context::getContext();
	}
	
	public function init()
	{
		parent::init();
		$pid = (int)Tools::GetValue('pid');	//удаление из списка
		if ($pid > 0) {
			$sql = 'DELETE FROM '._DB_PREFIX_.self::TABLE_NAME.' WHERE id_product='.$pid.' AND id_customer='.$this->context->customer->id;
			Db::getInstance()->execute($sql);
		}
	}	

	public function initContent()
	{
		parent::initContent();
		
		$this->page_name = 'Мой список отслеживания';
		$this->display_column_left = false;
		parent::initContent();
		
		$this->context->smarty->assign(array(
				'product_list' => $this->GetProductList($this->context->customer->id),
		));
		
		$this->setTemplate('userlistintake.tpl');
	}
	
	public function GetProductList($id_customer) 
	{
		$r = array();
		$sql = 'SELECT id_product FROM '._DB_PREFIX_.self::TABLE_NAME.' WHERE id_customer='.$id_customer;			
		//ddd($sql);
		if ($results = Db::getInstance()->ExecuteS($sql)) {
			$i = 0;
			foreach ($results as $row) {
				$r[$i]['id_product'] = $row['id_product'];
				$r[$i]['product_name'] = Product::getProductName($row['id_product']);
				if (isset(Image::getImages((int)$this->context->language->id, $row['id_product'])[0]['id_image'])) {
					$image_id = Image::getImages((int)$this->context->language->id, $row['id_product'])[0]['id_image'];
					$image_path = Image::getImgFolderStatic($image_id);
					$r[$i]['image'] = $this->context->link->getPageLink('index',true).'img/p/'.$image_path.$image_id.'.jpg';
				} else {
					$r[$i]['image'] = $this->context->link->getPageLink('index',true).'img/p/ru-default-medium_default.jpg';
				}

				$r[$i]['id_category'] = Product::getProductCategories($row['id_product'])[0];
				$category_name = Category::getCategoryInformations(array($r[$i]['id_category']))[$r[$i]['id_category']]['link_rewrite'];
				$p = new Product($row['id_product']);				
				$r[$i]['product_url'] = $this->context->link->getPageLink('index',true).
									$category_name.
									"/".
									substr($p->getLink(), strlen($this->context->link->getPageLink('index',true)));
				$i++;
			}
		}
		return $r;
	}
}
