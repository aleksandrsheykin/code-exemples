<?php
/**
* Oppa nihuya
* 
*/
if (!defined('_PS_VERSION_'))
  exit;
 
class VkClass extends ObjectModel
{
	
	static public function GetVkUrl() 
	{
		$vk_id =  Configuration::get('id_app');
		$vk_key =  Configuration::get('security_key');
		$vk_url = Tools::getHttpHost(true).__PS_BASE_URI__;
		$url = 'http://oauth.vk.com/authorize';
		
		$params = array(
			'client_id'     => $vk_id,
			'redirect_uri'  => $vk_url,
			'response_type' => 'code'
		);
		
		$link = $url . '?' . urldecode(http_build_query($params));
		return $link;
	}	
	
	static public function GetData($code) 
	{
		$vk_id =  Configuration::get('id_app');
		$vk_key =  Configuration::get('security_key');
		$vk_url = Tools::getHttpHost(true).__PS_BASE_URI__;
		
		$result = false;
		$params = array(
			'client_id' => $vk_id,
			'client_secret' => $vk_key,
			'code' => $code,
			'redirect_uri' => $vk_url
		);

		$token = json_decode(file_get_contents('https://oauth.vk.com/access_token' . '?' . urldecode(http_build_query($params))), true);
	
		if (isset($token['access_token'])) {
			$params = array(
				'uids'         => $token['user_id'],
				'fields'       => 'uid,first_name,last_name,screen_name,sex,bdate,photo_big',
				'access_token' => $token['access_token']
			);
		}

		$userInfo = json_decode(file_get_contents('https://api.vk.com/method/users.get' . '?' . urldecode(http_build_query($params))), true);
		if (isset($userInfo['response'][0]['uid'])) {
			$userInfo = $userInfo['response'][0];
			$result = true;
		}
		
		if ($result) {
			if (VkClass::uExists($userInfo['uid']))
			{
				VkClass::uAuth((int)$userInfo['uid']);
			} else {
				VkClass::CreateUser($userInfo['uid'], $userInfo['first_name'], $userInfo['last_name'], $userInfo['bdate'], $userInfo['sex']);
			}
		}		
	}
	
	private function uExists($vkid)
	{
		$sql = 'SELECT id_customer FROM '._DB_PREFIX_.'customer WHERE vkid='.(int)$vkid;
		if ($row = Db::getInstance()->getRow($sql))
			return $row['id_customer'];
		return false;
	}
	
	private function uAuth($vkuser)
	{
		if (is_object($vkuser))	{
			$customer = $vkuser;
		} else {
			$customer = new Customer($vkuser);
			$customer->getByEmail($vkuser.'@vk.com');
		}
		
		if (!$customer->id)
			Tools::displayError('Authentication failed.');
		else
		{
			$Context = Context::getContext();
			$Context->cookie->id_compare = isset($Context->cookie->id_compare) ? $Context->cookie->id_compare: CompareProduct::getIdCompareByIdCustomer($customer->id);
			$Context->cookie->id_customer = (int)($customer->id);
			$Context->cookie->customer_lastname = $customer->lastname;
			$Context->cookie->customer_firstname = $customer->firstname;
			$Context->cookie->logged = 1;
			$customer->logged = 1;
			$Context->cookie->is_guest = $customer->isGuest();
			$Context->cookie->passwd = $customer->passwd;
			$Context->cookie->email = $customer->email;
			
			// Add customer to the context
			$Context->customer = $customer;
			
			if (Configuration::get('PS_CART_FOLLOWING') && (empty($Context->cookie->id_cart) || Cart::getNbProducts($Context->cookie->id_cart) == 0) && $id_cart = (int)Cart::lastNoneOrderedCart($Context->customer->id))
				$Context->cart = new Cart($id_cart);
			else
			{
				$id_carrier = (int)$Context->cart->id_carrier;
				$Context->cart->id_carrier = 0;
				$Context->cart->setDeliveryOption(null);
				$Context->cart->id_address_delivery = (int)Address::getFirstCustomerAddressId((int)($customer->id));
				$Context->cart->id_address_invoice = (int)Address::getFirstCustomerAddressId((int)($customer->id));
			}
			$Context->cart->id_customer = (int)$customer->id;
			$Context->cart->secure_key = $customer->secure_key;


			$Context->cart->save();
			$Context->cookie->id_cart = (int)$Context->cart->id;
			$Context->cookie->write();
			$Context->cart->autosetProductAddress();

			Hook::exec('actionAuthentication');

			// Login information have changed, so we check if the cart rules still apply
			CartRule::autoRemoveFromCart($Context);
			CartRule::autoAddToCart($Context);

			if (($back = Tools::getValue('back')) && $back == Tools::secureReferrer($back))
				Tools::redirect(html_entity_decode($back));
			Tools::redirect('index.php?controller=my-account');
		}
	}
	
	private function CreateUser($vkid, $firstname, $lastname, $bday, $sex) {
		$u = new Customer();
		$bday = strtotime($bday);
		$bday = date("Y-m-d", $bday);
		$u->birthday = $bday;
		$u->firstname = $firstname;
		$u->lastname = $lastname;
		$u->id_gender = (int)$sex-1;
		$u->email = $vkid.'@vk.com';
		$u->passwd = md5(md5($vkid));
		$u->last_passwd_gen = md5(md5($vkid));
		$u->active = 1;
		if ($u->add(true, true)) {
			$sql = 'UPDATE '._DB_PREFIX_.'customer SET vkid = '.$vkid.' WHERE id_customer = '.$u->id;
			Db::getInstance()->execute($sql);
			VkClass::uAuth($u);
		}
	}
}	