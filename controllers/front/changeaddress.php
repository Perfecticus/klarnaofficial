<?php
/**
 * 2015 Prestaworks AB.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@prestaworks.se so we can send you a copy immediately.
 *
 *  @author    Prestaworks AB <info@prestaworks.se>
 *  @copyright 2015 Prestaworks AB
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of Prestaworks AB
 */

use Symfony\Component\Translation\TranslatorInterface;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\ObjectPresenter;

class KlarnaOfficialChangeAddressModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;
    public $display_column_right = false;
    public $ssl = true;

    public function init()
    {
        $has_changed = false;
        $klarnadata = Tools::file_get_contents('php://input');
        
        $klarnadata = json_decode($klarnadata);
        if ($klarnadata == false) {
            //something went wrong with the data, redirect.
            $this->redirectKCO();
        }
        $shipping_address = $klarnadata->shipping_address;
        if (!isset($shipping_address->country)) {
            //NO COUNTRY SET!
            $this->redirectKCO();
        }
        $country_iso = $shipping_address->country;

        $shipping_options = array();
        $id_cart = (int) Tools::getValue("cartid");
        $cart = new Cart($id_cart);

        $carrieraddress = new Address($cart->id_address_delivery);
        $country_on_cart = new Country($carrieraddress->id_country);
        if (Tools::strtolower($country_iso) != Tools::strtolower($country_on_cart->iso_code)) {
            $has_changed = true;
            if ((int)$cart->id_customer > 0) {
                //registered customer, check address
                if ((int)$carrieraddress->id_customer != (int)$cart->id_customer ||
                Tools::strtolower($country_iso) != Tools::strtolower($country_on_cart->iso_code)) {
                    //Not customers address, or country not correct for customer. Create a new address for the customer.
                    $address = new Address();
                    $firstname = (isset($shipping_address->given_name) ? $shipping_address->given_name : 'Missing');
                    $address->firstname = $this->module->truncateValue($firstname, 32, true);
                    $lastname = (isset($shipping_address->family_name) ? $shipping_address->family_name : 'Missing');
                    $address->lastname = $this->module->truncateValue($lastname, 32, true);
                    if (isset($shipping_address->street_address)) {
                        $address1 = $shipping_address->street_address;
                    } else {
                        $address1 = 'Missing';
                    }
                    
                    if (isset($shipping_address->care_of) && Tools::strlen($shipping_address->care_of) > 0) {
                        $address->address1 = $shipping_address->care_of;
                        $address->address2 = $address1;
                    } else {
                        $address->address1 = $address1;
                    }
                    $id_for_new_country = Country::getByIso($country_iso);
                    if (isset($shipping_address->postal_code)) {
                        $postal_code = $shipping_address->postal_code;
                    } else {
                        $postal_code = '00000';
                    }
                    $address->postcode = $postal_code;
                    
                    if (isset($shipping_address->phone)) {
                        $phone = $shipping_address->phone;
                    } else {
                        $phone = '0000000000';
                    }
                    $address->phone = $phone;
                    $address->phone_mobile = $phone;
                    $address->city = (isset($shipping_address->city) ? $shipping_address->city : 'Missing');
                    $address->id_country = $id_for_new_country;
                    $address->id_customer = (int)$cart->id_customer;
                    $address->alias = 'Klarna Address '.date('Ymd');
                    
                    if ($id_for_new_country>0) {
                        $address->add();
                        $new_address_id = $address->id;
                    } else {
                        PrestaShopLogger::addLog("Klarna Country not active: $country_iso", 3, null, '', 0, true);
                    }
                }
            } else {
                $id_country = (int) Country::getByIso($country_iso, true);
                $new_country = new Country($id_country);
                $new_address_id = (int) Configuration::get('KCOV3_'.$new_country->iso_code);
                if ($new_address_id == 0) {
                    $this->module->createAddress(
                        $new_country->iso_code,
                        'KCOV3_'.$new_country->iso_code,
                        'City',
                        'Country',
                        'KCOV3_'.$new_country->iso_code
                    );
                    $new_address_id = (int) Configuration::get('KCOV3_'.$new_country->iso_code);
                }
            }
        } else {
            echo json_encode($klarnadata);
            exit;
        }
        if ($has_changed == true) {
            $cart->id_address_delivery = $new_address_id;
            $carrieraddress = new Address($cart->id_address_delivery);
            $country_on_cart = new Country($carrieraddress->id_country);

            $update_sql = 'UPDATE '._DB_PREFIX_.
                'cart_product SET id_address_delivery='.
                (int) $new_address_id.
                ' WHERE id_cart='.
                (int) $cart->id;
                
            Db::getInstance()->execute($update_sql);
            $update_sql = 'UPDATE '._DB_PREFIX_.
            'customization SET id_address_delivery='.
            (int) $new_address_id.
            ' WHERE id_cart='.
            (int) $cart->id;
            
            Db::getInstance()->execute($update_sql);

            $this->context->cart = $cart;
            $this->context->country = $country_on_cart;
            $id_currency = (int) ($country_on_cart->id_currency ? $country_on_cart->id_currency : $cart->id_currency);
            $tmp_currency = new Currency($id_currency);
            if (!isset($this->context->currency)) {
                $this->context->currency = $tmp_currency;
                global $currency; // Compatibility 1.4
                $currency = $tmp_currency;
            } else {
                foreach (get_object_vars($tmp_currency) as $key => $value) {
                    $this->context->currency->$key = $value;
                }
            }
            $this->context->cart->id_currency = (int)$this->context->currency->id;
            Tools::setCurrency($this->context->cookie);
            $klarnadata->purchase_country=$this->context->country->iso_code;
            $klarnadata->purchase_currency=strtolower($this->context->currency->iso_code);
            $cart->update();
            $cart->getPackageList(true);
        }
        
        
        require_once dirname(__FILE__).'/../../libraries/commonFeatures.php';
        $KlarnaCheckoutCommonFeatures = new KlarnaCheckoutCommonFeatures();
        $language = new Language((int) $cart->id_lang);
        if (isset($this->module->shippingreferences[$language->iso_code])) {
            $shippingReference =  $this->module->shippingreferences[$language->iso_code];
        } else {
            $shippingReference =  $this->module->shippingreferences["en"];
        }
        
        if (isset($this->module->wrappingreferences[$language->iso_code])) {
            $wrappingreference = $this->module->wrappingreferences[$language->iso_code];
        } else {
            $wrappingreference = $this->module->wrappingreferences["en"];
        }
        $cart->setDeliveryOption($cart->getDeliveryOption());
        $order_lines = $KlarnaCheckoutCommonFeatures->BuildCartArray(
            $cart,
            $shippingReference,
            $wrappingreference,
            $this->module->l('Wrapping', 'KlarnaOfficialChangeAddressModuleFrontController'),
            $this->module->l('Discount', 'KlarnaOfficialChangeAddressModuleFrontController')
        );
        foreach ($cart->getDeliveryOptionList(null, true) as $options) {
            foreach ($options as $option) {
                foreach ($option["carrier_list"] as $carrieroption) {
                    $carrierobject = $carrieroption["instance"];
                    $shipping_option = array();
                    $shipping_option["id"] = $carrierobject->id;
                    if ($cart->id_carrier == $carrierobject->id) {
                        $shipping_option["preselected"] = true;
                    } else {
                        $shipping_option["preselected"] = false;
                    }
                    $shipping_option["name"] = $carrierobject->name;
                    $shipping_option["description"] = $carrierobject->delay[(int)$cart->id_lang];
                    $shipping_option["price"] = $option["total_price_with_tax"] * 100;
                    $tax_amount = $option["total_price_with_tax"] - $option["total_price_without_tax"];
                    $tax_amount = $tax_amount * 100;
                    $shipping_option["tax_amount"] = $tax_amount;
                    $shipping_tax_rate = $carrierobject->getTaxesRate($carrieraddress);
                    $shipping_option["tax_rate"] = $shipping_tax_rate*100;
                    $shipping_options[] = $shipping_option;
                }
            }
        }
        $klarnadata->shipping_options = $shipping_options;
        unset($klarnadata->selected_shipping_option);// = null;
        $klarnadata->order_lines = $order_lines;
        
        // $totalCartValue = $cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING);
        $totalCartValue = $cart->getOrderTotal(true, Cart::BOTH);
        // $totalCartValue_tax_excl = $cart->getOrderTotal(false, Cart::BOTH_WITHOUT_SHIPPING);
        $totalCartValue_tax_excl = $cart->getOrderTotal(false, Cart::BOTH);
        $total_tax_value = $totalCartValue - $totalCartValue_tax_excl;
        $klarnadata->order_amount = $totalCartValue * 100;
        $klarnadata->order_tax_amount = $total_tax_value * 100;
        echo json_encode($klarnadata);
        exit;
    }
    
    
    public function redirectKCO($url = false)
    {
        header('HTTP/1.1 303 See Other');
        header('Cache-Control: no-cache');

        if ($url === false) {
            $url = $this->context->link->getModuleLink(
                'klarnaofficial',
                'checkoutklarnakco',
                array("haserror" => 1),
                true
            );
        }
        Tools::redirect($url);
        exit;
    }
}
