<?php
class IntserCustomerDiscountsCore extends ObjectModel
{   
	public $id_customer;
    public $id_category;
    public $discount;
    public static $definition = array(
    'table' => 'intser_customerdiscount',
    'primary' => 'id',
    'fields' => array(
    'id_category' => array('type' => self::TYPE_INT, 'required' => true),
	'id_customer' => array('type' => self::TYPE_INT, 'required' => true),
	'discount' => array('type' => self::TYPE_INT, 'required' => true),
    ),
    );
    protected $webserviceParameters = array();
}