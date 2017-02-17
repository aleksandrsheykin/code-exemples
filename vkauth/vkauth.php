<?php
/**************
*
* module vkauth
* 
**************/
if (!defined('_PS_VERSION_'))
  exit;
 
include_once(dirname(__FILE__).'/vkclass.php'); 
 
class VkAuth extends Module
{
	public function __construct()
	{
		$this->name = 'VkAuth';
		$this->tab = 'others';
		$this->version = '1.0.0';
		$this->author = 'Lun';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_); 
		$this->bootstrap = true;

		parent::__construct();
		$this->displayName = $this->l('Авторизация ВК');
		$this->description = $this->l('Авторизация через социальную сеть вконтакте. Чтоб модуль работал, необходимо создать приложение для вашего сайта здесь (https://vk.com/editapp?act=create) и вписать полученные ID приложения и защищенный ключ в настройки модуля.');

		$this->confirmUninstall = $this->l('Вы уверены, что хотите удалить модуль?');		
	}
	
	public function install()
	{
		if (!parent::install()
			|| !$this->registerHook('displayNav') 
		)
			return false;
			
		$sql = 'ALTER TABLE '._DB_PREFIX_.'customer ADD vkid INT(11)';
		if (!Db::getInstance()->execute($sql))
			die('Error from sql execute');
		
		return true;
	}

	public function uninstall()
	{
		if (!parent::uninstall())
			return false;
		
		$sql = 'DELETE FROM '._DB_PREFIX_.'customer WHERE vkid IS NOT NULL';	
		if (!Db::getInstance()->execute($sql))
			die('Error from sql execute');
		
		$sql = 'ALTER TABLE '._DB_PREFIX_.'customer DROP COLUMN vkid';	
		if (!Db::getInstance()->execute($sql))
			die('Error from sql execute');
		
		Configuration::updateValue('id_app', '');
		Configuration::updateValue('security_key', '');		
		
		return true;
	}
	
	public function getContent()
	{
		$output = null;
	 
		if (Tools::isSubmit('submit'.$this->name))
		{
			$id_app = strval(Tools::getValue('id_app'));
			$security_key = strval(Tools::getValue('security_key'));
			if (!$id_app
			  || empty($id_app)
			  || !Validate::isGenericName($id_app)
			  || !$security_key
			  || empty($security_key)
			  || !Validate::isGenericName($security_key))
				$output .= $this->displayError($this->l('Invalid Configuration value'));
			else
			{
				Configuration::updateValue('id_app', $id_app);
				Configuration::updateValue('security_key', $security_key);
				$output .= $this->displayConfirmation($this->l('Settings updated'));
			}
		}
		return $output.$this->displayForm();
	}

	public function hookDisplayNav($params)
	{
		if (!Configuration::get('id_app') 
			&& empty(Configuration::get('id_app'))
			&& !Configuration::get('security_key') 
			&& empty(Configuration::get('security_key')))
		{
			return false;
		}
		
		if (Tools::GetValue('code')) {
			VkClass::GetData(Tools::GetValue('code'));			
		} else 
		{		
			$this->smarty->assign(array(
				'vkurl' => VkClass::GetVkUrl()
			));		
			return $this->display(__FILE__, 'headerbtn.tpl');
		}
	}
	
	public function displayForm()
	{
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
		 
		$fields_form[0]['form'] = array(
			'legend' => array(
				'title' => 'Настройки',
			),
			'input' => array(
				array(
					'type' => 'text',
					'label' => 'ID приложения',
					'name' => 'id_app',
					'size' => 20,
					'required' => true
				),
				array(
					'type' => 'text',
					'label' => 'Защищённый ключ',
					'name' => 'security_key',
					'size' => 40,
					'required' => true
				)				
			),
			'submit' => array(
				'title' => 'Сохранить',
				'class' => 'button'
			)
		);
		 
		$helper = new HelperForm();
		 
		// Module, token and currentIndex
		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		 
		// Language
		$helper->default_form_language = $default_lang;
		$helper->allow_employee_form_lang = $default_lang;
		 
		$helper->title = $this->displayName;
		$helper->show_toolbar = true;
		$helper->toolbar_scroll = true;
		$helper->submit_action = 'submit'.$this->name;
		$helper->toolbar_btn = array(
			'save' =>
			array(
				'desc' => $this->l('Save'),
				'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
				'&token='.Tools::getAdminTokenLite('AdminModules'),
			),
			'back' => array(
				'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
				'desc' => $this->l('Back to list')
			)
		);
		 
		// Load current value
		$helper->fields_value['id_app'] = Configuration::get('id_app');
		$helper->fields_value['security_key'] = Configuration::get('security_key');
		 
		return $helper->generateForm($fields_form);
	}	
	
}	