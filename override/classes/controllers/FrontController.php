<?php
class FrontController extends FrontControllerCore
{
    /**
     * Initializes front controller: sets smarty variables,
     * class properties, redirects depending on context, etc.
     *
     * @global bool     $useSSL           SSL connection flag
     * @global Cookie   $cookie           Visitor's cookie
     * @global Smarty   $smarty
     * @global Cart     $cart             Visitor's cart
     * @global string   $iso              Language ISO
     * @global Country  $defaultCountry   Visitor's country object
     * @global string   $protocol_link
     * @global string   $protocol_content
     * @global Link     $link
     * @global array    $css_files
     * @global array    $js_files
     * @global Currency $currency         Visitor's selected currency
     *
     * @throws PrestaShopException
     */
    public function init()
    {
        /*
         * Globals are DEPRECATED as of version 1.5.0.1
         * Use the Context object to access objects instead.
         * Example: $this->context->cart
         */
        global $useSSL, $cookie, $smarty, $cart, $iso, $defaultCountry, $protocol_link, $protocol_content, $link, $css_files, $js_files, $currency;

        if (self::$initialized) {
            return;
        }

        self::$initialized = true;

        parent::init();

        // enable Symfony error handler if debug mode enabled
        $this->initDebugguer();

        // If current URL use SSL, set it true (used a lot for module redirect)
        if (Tools::usingSecureMode()) {
            $useSSL = true;
        }

        // For compatibility with globals, DEPRECATED as of version 1.5.0.1
        $css_files = $this->css_files;
        $js_files = $this->js_files;

        $this->sslRedirection();

        if ($this->ajax) {
            $this->display_header = false;
            $this->display_footer = false;
        }

        // If account created with the 2 steps register process, remove 'account_created' from cookie
        if (isset($this->context->cookie->account_created)) {
            unset($this->context->cookie->account_created);
        }

        ob_start();

        $protocol_link = (Configuration::get('PS_SSL_ENABLED') || Tools::usingSecureMode()) ? 'https://' : 'http://';
        $useSSL = ((isset($this->ssl) && $this->ssl && Configuration::get('PS_SSL_ENABLED')) || Tools::usingSecureMode()) ? true : false;
        $protocol_content = ($useSSL) ? 'https://' : 'http://';
        $link = new Link($protocol_link, $protocol_content);
        $this->context->link = $link;

        if ($id_cart = (int) $this->recoverCart()) {
            $this->context->cookie->id_cart = (int) $id_cart;
        }

        if ($this->auth && !$this->context->customer->isLogged($this->guestAllowed)) {
            Tools::redirect('index.php?controller=authentication' . ($this->authRedirection ? '&back=' . $this->authRedirection : ''));
        }

        /* Theme is missing */
        if (!is_dir(_PS_THEME_DIR_)) {
            throw new PrestaShopException(
                $this->trans(
                    'Current theme is unavailable. Please check your theme\'s directory name ("%s") and permissions.',
                    array(basename(rtrim(_PS_THEME_DIR_, '/\\'))),
                    'Admin.Design.Notification'
                )
            );
        }

		if (Configuration::get('PS_GEOLOCATION_ENABLED')) {
            if (($new_default = $this->geolocationManagement($this->context->country)) && Validate::isLoadedObject($new_default)) {
                $this->context->country = $new_default;
            }
        } elseif (Configuration::get('PS_DETECT_COUNTRY')) {
            $has_currency = isset($this->context->cookie->id_currency) && (int) $this->context->cookie->id_currency;
            $has_country = isset($this->context->cookie->iso_code_country) && $this->context->cookie->iso_code_country;
            $has_address_type = false;

            if ((int) $this->context->cookie->id_cart && ($cart = new Cart($this->context->cookie->id_cart)) && Validate::isLoadedObject($cart)) {
                $has_address_type = isset($cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}) && $cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
            }

            if ((!$has_currency || $has_country) && !$has_address_type) {
                $id_country = $has_country && !Validate::isLanguageIsoCode($this->context->cookie->iso_code_country) ?
                    (int) Country::getByIso(strtoupper($this->context->cookie->iso_code_country)) : (int) Tools::getCountry();

                $country = new Country($id_country, (int) $this->context->cookie->id_lang);

                if (!$has_currency && validate::isLoadedObject($country) && $this->context->country->id !== $country->id) {
                    $this->context->country = $country;
                    $this->context->cookie->id_currency = (int) Currency::getCurrencyInstance($country->id_currency ? (int) $country->id_currency : (int) Configuration::get('PS_CURRENCY_DEFAULT'))->id;
                    $this->context->cookie->iso_code_country = strtoupper($country->iso_code);
                }
            }
        }

        $currency = Tools::setCurrency($this->context->cookie);

        if (isset($_GET['logout']) || ($this->context->customer->logged && Customer::isBanned($this->context->customer->id))) {
            $this->context->customer->logout();

            Tools::redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null);
        } elseif (isset($_GET['mylogout'])) {
            $this->context->customer->mylogout();
            Tools::redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null);
        }

        /* Cart already exists */
        if ((int) $this->context->cookie->id_cart) {
            if (!isset($cart)) {
                $cart = new Cart($this->context->cookie->id_cart);
            }

            if (Validate::isLoadedObject($cart) && $cart->orderExists()) {
                PrestaShopLogger::addLog('Frontcontroller::init - Cart cannot be loaded or an order has already been placed using this cart', 1, null, 'Cart', (int) $this->context->cookie->id_cart, true);
                unset($this->context->cookie->id_cart, $cart, $this->context->cookie->checkedTOS);
                $this->context->cookie->check_cgv = false;
            } elseif ((int) (Configuration::get('PS_GEOLOCATION_ENABLED'))
                && !in_array(strtoupper($this->context->cookie->iso_code_country), explode(';', Configuration::get('PS_ALLOWED_COUNTRIES')))
                && $cart->nbProducts()
                && (int) (Configuration::get('PS_GEOLOCATION_NA_BEHAVIOR')) != -1
                && !FrontController::isInWhitelistForGeolocation()
                && !in_array($_SERVER['SERVER_NAME'], array('localhost', '127.0.0.1', '::1'))
            ) {
                /* Delete product of cart, if user can't make an order from his country */
                PrestaShopLogger::addLog('Frontcontroller::init - GEOLOCATION is deleting a cart', 1, null, 'Cart', (int) $this->context->cookie->id_cart, true);
                unset($this->context->cookie->id_cart, $cart);
            } elseif ($this->context->cookie->id_customer != $cart->id_customer || $this->context->cookie->id_lang != $cart->id_lang || $currency->id != $cart->id_currency) {
                // update cart values
                if ($this->context->cookie->id_customer) {
                    $cart->id_customer = (int) $this->context->cookie->id_customer;
                }
                $cart->id_lang = (int) $this->context->cookie->id_lang;
                $cart->id_currency = (int) $currency->id;
                $cart->update();
            }
            /* Select an address if not set */
            if (isset($cart) && (!isset($cart->id_address_delivery) || $cart->id_address_delivery == 0 ||
                !isset($cart->id_address_invoice) || $cart->id_address_invoice == 0) && $this->context->cookie->id_customer) {
                $to_update = false;
                if (!isset($cart->id_address_delivery) || $cart->id_address_delivery == 0) {
                    $to_update = true;
                    $cart->id_address_delivery = (int) Address::getFirstCustomerAddressId($cart->id_customer);
                }
                if (!isset($cart->id_address_invoice) || $cart->id_address_invoice == 0) {
                    $to_update = true;
                    $cart->id_address_invoice = (int) Address::getFirstCustomerAddressId($cart->id_customer);
                }
                if ($to_update) {
                    $cart->update();
                }
            }
        }

        if (!isset($cart) || !$cart->id) {
            $cart = new Cart();
            $cart->id_lang = (int) $this->context->cookie->id_lang;
            $cart->id_currency = (int) $this->context->cookie->id_currency;
            $cart->id_guest = (int) $this->context->cookie->id_guest;
            $cart->id_shop_group = (int) $this->context->shop->id_shop_group;
            $cart->id_shop = $this->context->shop->id;
            if ($this->context->cookie->id_customer) {
                $cart->id_customer = (int) $this->context->cookie->id_customer;
                $cart->id_address_delivery = (int) Address::getFirstCustomerAddressId($cart->id_customer);
                $cart->id_address_invoice = (int) $cart->id_address_delivery;
            } else {
                $cart->id_address_delivery = 0;
                $cart->id_address_invoice = 0;
            }

            // Needed if the merchant want to give a free product to every visitors
            $this->context->cart = $cart;
            CartRule::autoAddToCart($this->context);
        } else {
            $this->context->cart = $cart;
        }

        $this->context->cart->checkAndUpdateAddresses();

        $this->context->smarty->assign('request_uri', Tools::safeOutput(urldecode($_SERVER['REQUEST_URI'])));

        // Automatically redirect to the canonical URL if needed
        if (!empty($this->php_self) && !Tools::getValue('ajax')) {
            $this->canonicalRedirection($this->context->link->getPageLink($this->php_self, $this->ssl, $this->context->language->id));
        }

        Product::initPricesComputation();

        $display_tax_label = $this->context->country->display_tax_label;
        if (isset($cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}) && $cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}) {
            $infos = Address::getCountryAndState((int) $cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
            $country = new Country((int) $infos['id_country']);
            $this->context->country = $country;
            if (Validate::isLoadedObject($country)) {
                $display_tax_label = $country->display_tax_label;
            }
        }

        /*
         * These shortcuts are DEPRECATED as of version 1.5.0.1
         * Use the Context to access objects instead.
         * Example: $this->context->cart
         */
        self::$cookie = $this->context->cookie;
        self::$cart = $cart;
        self::$smarty = $this->context->smarty;
        self::$link = $link;
        $defaultCountry = $this->context->country;

        $this->displayMaintenancePage();

        if (Country::GEOLOC_FORBIDDEN == $this->restrictedCountry) {
            $this->displayRestrictedCountryPage();
        }

        $this->iso = $iso;
        $this->context->cart = $cart;
        $this->context->currency = $currency;

        Hook::exec('actionFrontControllerAfterInit');
    }
}