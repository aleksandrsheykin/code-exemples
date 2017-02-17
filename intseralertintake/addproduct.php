<?php

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');
require_once(dirname(__FILE__).'/intseralertintake.php');

$module = new IntserAlertIntake();
$TABLE_NAME = 'intser_customerintake';
$context = Context::getContext();
$id_product = (int)Tools::getValue('id_product');
$date_ins = Tools::getValue('date');


if ($context->customer->isLogged())
{
	//$logger->logDebug($sql);
	$sql = 'SELECT COUNT(*) FROM '._DB_PREFIX_.$TABLE_NAME.' WHERE id_customer='.$context->customer->id.' AND id_product='.$id_product;
	if (!Db::getInstance()->getValue($sql)) {
		Db::getInstance()->insert($TABLE_NAME, array(
			'id_product' => $id_product,
			'id_customer'=> $context->customer->id,
		));
		echo "При поступлении этого товара на склад, мы Вас оповестим";
	} else {
		echo "Данный товар уже добавлен в список оповещения";
	}
}