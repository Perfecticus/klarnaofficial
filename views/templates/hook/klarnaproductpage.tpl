{*
* 2015 Prestaworks AB
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
*}
{*if true === $showV3Widget}
<klarna-placement data-id="7ae6543c-1081-474e-a6da-3c796e5edcee" data-purchase_amount="{$kcoproductPrice|escape:'html':'UTF-8'}"></klarna-placement>
<script src="https://us-library.klarnaservices.com/merchant.js?uci=b5b8cee7-458d-4649-89cc-62dab1a0b70b&country=US" async></script>
{/if*}
{if true === $showLegacyWidget}
<div style="height:70px; padding: 13px 19px 0;" 
     class="klarna-widget klarna-part-payment"
     data-eid="{$kcoeid|escape:'html':'UTF-8'}" 
     data-locale="{$klarna_locale|escape:'html':'UTF-8'}"
     data-price="{$kcoproductPrice|escape:'html':'UTF-8'}"
     data-layout="{$klarna_widget_layout|escape:'html':'UTF-8'}"
     data-invoice-fee="{$klarna_invoice_fee|escape:'html':'UTF-8'}">
</div>
{/if}