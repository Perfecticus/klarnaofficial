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


{if isset($klarna_checkout_cart_changed) && $klarna_checkout_cart_changed}
<div class="alert alert-warning">
    {l s='Your cart have changed.' mod='klarnaofficial'}<br />
    {l s='Please check all information below and then continue with the checkout.' mod='klarnaofficial'}
</div>
{/if}
{if isset($haserror) && $haserror}
<div class="alert alert-warning">
    {l s='Something went wrong.' mod='klarnaofficial'}<br />
    {l s='Please try again.' mod='klarnaofficial'}
</div>
{/if}
{if isset($KCO_SHOWLINK) && $KCO_SHOWLINK}
	<a
		href="{$link->getPageLink('order', true, NULL, 'step=1')|escape:'html':'UTF-8'}"
		class="button btn btn-default button-medium"
		title="{l s='Company customers / alternative payments' mod='klarnaofficial'}">
		<span>{l s='Company customers / alternative payments' mod='klarnaofficial'}<i class="icon-chevron-right right"></i></span>
	</a>
{/if}

{capture name=path}{l s='Checkout' mod='klarnaofficial'}{/capture}

{if isset($klarna_error)}
{if isset($connectionerror)}
    {if $connectionerror}
        <a href="{$link->getPageLink("order", true)|escape:'html':'UTF-8'}" class="button btn btn-default button-medium">{l s='Go to checkout' mod='klarnaofficial'}</a><br /><br />
    {/if}
{/if}
<div class="alert alert-warning">
    {if $klarna_error=='empty_cart'}
    {l s='Your cart is empty' mod='klarnaofficial'}
    {else}
    {$klarna_error|escape:'html':'UTF-8'}
    {/if}
</div>
{else}
{if isset($vouchererrors) && $vouchererrors!=''}
<div class="alert alert-warning">
    {$vouchererrors|escape:'html':'UTF-8'}
</div>
{/if}

<script type="text/javascript">
    // <![CDATA[
    var currencySign = '{$currencySign|escape:'javascript':'UTF-8'}';
    var currencyRate = '{$currencyRate|floatval}';
    var currencyFormat = '{$currencyFormat|intval}';
    var currencyBlank = '{$currencyBlank|intval}';
    var txtProduct = "{l s='product' js=1 mod='klarnaofficial'}";
    var txtProducts = "{l s='products' js=1 mod='klarnaofficial'}";
    var freeShippingTranslation = "{l s='Free Shipping!' js=1 mod='klarnaofficial'}";
    var kcourl = "{$kcourl|escape:'javascript':'UTF-8'}";
    // ]]>
</script>
<style type="text/css">
    .kco-btn--default {
        background: #343434;
        color: #fff;
    }
    .kco-btn--default:hover {
        background: #191919;
        color: #fff;
    }
    .kco-btn--default:active, .kco-btn--default:focus {
        background: #343434;
        color: #fff;
    }
</style>
<div class="kco-cf kco-main">
    <div id="kco_cart_summary_div">
		{include file="$tpl_dir/shopping-cart.tpl"}
    </div><!-- /#kco_cart_summary_div -->
    <div class="row">
        {if isset($left_to_get_free_shipping) AND $left_to_get_free_shipping>0}
        <div class="col-xs-12">
            <div class="kco-infobox">
                {l s='By shopping for' mod='klarnaofficial'}&nbsp;<strong>{convertPrice price=$left_to_get_free_shipping}</strong>&nbsp;{l s='more, you will qualify for free shipping.' mod='klarnaofficial'}
            </div>
        </div>
        {/if}
        <div class="col-xs-12 col-md-4">
            {if isset($delivery_option_list) && $controllername != 'checkoutklarnakco'}
            <div class="kco-box">
                <span class="kco-step-heading">{l s='Step 1' mod='klarnaofficial'}</span>
                <h4 class="kco-title kco-title--step">
                    {l s='Carrier' mod='klarnaofficial'}
                </h4>
                {if $show_prefil_link}
                <div class="kco-infobox">
                <a href="{$link->getModuleLink('klarnaofficial', $controllername, ['oktoprefill'=>true], true)|escape:'html':'UTF-8'}">{l s='Prefill payment window' mod='klarnaofficial'}</a>
                </div>
                {/if}
                {if $no_active_countries > 1}
                <span class="kco-step-heading">{l s='Change country' mod='klarnaofficial'}</span>
                <form action="{$link->getModuleLink('klarnaofficial', $controllername, [], true)|escape:'html':'UTF-8'}" method="post" id="kco_change_country">
                    <select name="kco_change_country" class="kco-select kco-select--full kco-select--margin" onchange="$('#kco_change_country').submit();">
                        {if $show_sweden}<option value="sv" {if $kco_selected_country=='SE'}selected="selected"{/if}>{l s='Sweden' mod='klarnaofficial'}</option>{/if}
                        {if $show_norway}<option value="no" {if $kco_selected_country=='NO'}selected="selected"{/if}>{l s='Norway' mod='klarnaofficial'}</option>{/if}
                        {if $show_finland}<option value="fi" {if $kco_selected_country=='FI'}selected="selected"{/if}>{l s='Finland' mod='klarnaofficial'}</option>{/if}
                        {if $show_germany}<option value="de" {if $kco_selected_country=='DE'}selected="selected"{/if}>{l s='Germany' mod='klarnaofficial'}</option>{/if}
                        {if $show_austria}<option value="at" {if $kco_selected_country=='AT'}selected="selected"{/if}>{l s='Austria' mod='klarnaofficial'}</option>{/if}
                        {if $show_uk}<option value="gb" {if $kco_selected_country=='GB'}selected="selected"{/if}>{l s='United Kingdom' mod='klarnaofficial'}</option>{/if}
                        {if $show_us}<option value="us" {if $kco_selected_country=='US'}selected="selected"{/if}>{l s='United States' mod='klarnaofficial'}</option>{/if}
                        {if $show_nl}<option value="nl" {if $kco_selected_country=='NL'}selected="selected"{/if}>{l s='Netherlands' mod='klarnaofficial'}</option>{/if}
                    </select>
                </form><!-- /form#kco_change_country -->
                {/if}
                <form action="{$link->getModuleLink('klarnaofficial', $controllername, [], true)|escape:'html':'UTF-8'}" method="post" id="klarnacarrier">
                {foreach $delivery_option_list as $id_address => $option_list}
                <ul class="kco-sel-list has-tooltips">
                    {foreach $option_list as $key => $option}
                    <li class="kco-sel-list__item {if isset($delivery_option[$id_address|intval]) && $delivery_option[$id_address|intval] == $key}selected{/if}">
                        <input class="kco-sel-list__item__radio" type="radio" name="delivery_option[{$id_address|intval}]" onchange="$('#klarnacarrier').submit()" id="delivery_option_{$id_address|intval}_{$option@index|escape:'htmlall':'UTF-8'}" value="{$key|escape:'htmlall':'UTF-8'}" {if isset($delivery_option[$id_address|intval]) && $delivery_option[$id_address|intval] == $key}checked="checked"{/if} />
                        <label for="delivery_option_{$id_address|escape:'htmlall':'UTF-8'}_{$option@index|escape:'htmlall':'UTF-8'}" class="kco-sel-list__item__label">
                            <span class="kco-sel-list__item__status">
                                <i class="icon-ok icon-check"></i>
                            </span>
                            <span class="kco-sel-list__item__title">
                                {if $option.unique_carrier}
                                    {foreach $option.carrier_list as $carrier}
                                        {$carrier.instance->name|escape:'html':'UTF-8'}
                                    {/foreach}
                                {/if}
                            </span>
                            <span class="kco-sel-list__item__nbr">
                                {if $option.total_price_with_tax && !$free_shipping}
                                    {if $use_taxes == 1}
                                    {convertPrice price=$option.total_price_with_tax}
                                    {else}
                                    {convertPrice price=$option.total_price_without_tax}
                                    {/if}
                                {else}
                                    {l s='Free!' mod='klarnaofficial'}
                                {/if}
                            </span>
                            <span class="kco-sel-list__item__info">
                                {if $option.unique_carrier}
                                    {if isset($carrier.instance->delay[$cookie->id_lang|intval])}
                                        {$carrier.instance->delay[$cookie->id_lang|intval]}&nbsp;  
                                    {/if}
                                {/if}
                                {if count($option_list) > 1}
                                    {if $option.is_best_grade}
                                        {if $option.is_best_price}
                                            &ndash;&nbsp;{l s='The best price and speed' mod='klarnaofficial'}
                                        {else}
                                            &ndash;&nbsp;{l s='The fastest' mod='klarnaofficial'}
                                        {/if}
                                    {else}
                                        {if $option.is_best_price}
                                            &ndash;&nbsp;{l s='The best price' mod='klarnaofficial'}
                                        {/if}
                                    {/if}
                                {/if}
                            </span>
                        </label>
                        {if !$option.unique_carrier}
                        <table class="delivery_option_carrier {if isset($delivery_option[$id_address]) && $delivery_option[$id_address] == $key}selected{/if}">
                        {foreach $option.carrier_list as $carrier}
                            <tr>
                                <td class="first_item">
                                    <input type="hidden" value="{$carrier.instance->id|intval}" name="id_carrier" />
                                    {if $carrier.logo}
                                    <img src="{$carrier.logo|escape:'htmlall':'UTF-8'}" alt="{$carrier.instance->name|escape:'htmlall':'UTF-8'}"/>
                                    {/if}
                                </td>
                                <td>
                                    {$carrier.instance->name|escape:'htmlall':'UTF-8'}
                                </td>
                            </tr>
                        {/foreach}
                        </table><!-- /.delivery_option_carrier -->
                        {/if}
                    </li>
                    {/foreach}
                </ul>
                {/foreach}
                </form>
            </div><!-- /.kco-box -->
            {/if}
            <div class="kco-box">
                <form action="{$link->getModuleLink('klarnaofficial', $controllername, [], true)|escape:'html':'UTF-8'}" method="post" id="klarnavoucher">
                    <span class="kco-step-heading">{l s='Add Voucher' mod='klarnaofficial'}</span>
                    <div class="kco-target">
                        <div class="kco-input-group">
                            <input type="text" class="kco-input kco-input--text discount_name" id="discount_name" name="discount_name" placeholder="{l s='Enter discount code' mod='klarnaofficial'}" value="{if isset($discount_name) && $discount_name != ''}{$discount_name|escape:'htmlall':'UTF-8'}{/if}" />
                            <button type="submit" id="submitAddDiscount" name="submitAddDiscount" class="kco-btn kco-btn--default">{l s='Save' mod='klarnaofficial'}</button>
                            <input type="hidden" name="submitDiscount" />
                        </div>
                        {if sizeof($discounts)}
                        <ul id="klarnacheckoutvouchers" class="kco-del-list clear">
                            {foreach $discounts as $discount}
                            <li>
                                <span class="kco-del-list__title">{$discount.name|escape:'htmlall':'UTF-8'}</span><span class="kco-del-list__nbr">{if !$priceDisplay}{displayPrice price=$discount.value_real*-1}{else}{displayPrice price=$discount.value_tax_exc*-1}{/if}</span>
                                {if strlen($discount.code)}<a href="{$link->getModuleLink('klarnaofficial', $controllername, [deleteDiscount => $discount.id_discount|intval], true)|escape:'html'}" class="kco-del-list__btn" title="{l s='Delete' mod='klarnaofficial'}"><i class="icon-remove"></i></a>{/if}
                            </li>
                            {/foreach}
                        </ul>
                        {/if}
                    </div>
                </form><!-- /#klarnavoucher -->
            </div><!-- /.kco-box -->
            {if $controllername == 'checkoutklarnakco'}
                <div class="kco-box"></div>
            {/if}
            <div class="message_container kco-box">
                <form action="{$link->getModuleLink('klarnaofficial', $controllername, [], true)|escape:'html':'UTF-8'}" method="post" id="klarnamessage">

                    <span class="kco-step-heading">{l s='Add Message' mod='klarnaofficial'}</span>
 
                    <p id="message-area" class="kco-target">
                        <textarea class="kco-input kco-input--area kco-input--full" id="message" name="message" rows="4" cols="50" placeholder="{l s='Add additional information to your order (optional)' mod='klarnaofficial'}">{$message.message|escape:'htmlall':'UTF-8'}</textarea>
                        <input type="submit" name="savemessagebutton" class="kco-btn kco-btn--default" id="savemessagebutton" value="{l s='Save' mod='klarnaofficial'}" />
                    </p>
                </form><!-- /#klarnamessage -->
            </div><!-- /.message_container.kco-box -->
            {if $giftAllowed==1}
            <div class="gift_container kco-box">
                <form action="{$link->getModuleLink('klarnaofficial', $controllername, [], true)|escape:'html':'UTF-8'}" method="post" id="klarnagift">
                    <h4 id="giftwrap-title" class="kco-title kco-trigger {if $gift_message == '' && (!isset($gift) || $gift==0)}kco-trigger--inactive{/if}">
                        {l s='Giftwrapping' mod='klarnaofficial'}
                    </h4>
                    <p id="giftwrap-message" class="kco-target" {if $gift_message == '' && (!isset($gift) || $gift==0)}style="display: none;"{/if}>
                        <textarea class="kco-input kco-input--area kco-input--full" id="gift_message" name="gift_message" placeholder="{l s='Gift message (optional)' mod='klarnaofficial'}">{$gift_message|escape:'htmlall':'UTF-8'}</textarea>
                        <input type="hidden" name="savegift" id="savegift" value="1" />
                        <input type="submit" name="savegiftbutton" class="kco-btn kco-btn--default" id="savegiftbutton" value="{l s='Save' mod='klarnaofficial'}" />
                        <span class="kco-check-group fl-r">
                            <input type="checkbox" onchange="$('#klarnagift').submit();" class="giftwrapping_radio" id="gift" name="gift" value="1"{if isset($gift) AND $gift==1} checked="checked"{/if} />
                            <span id="giftwrappingextracost">{l s='Additional cost:' mod='klarnaofficial'} {displayPrice price=$gift_wrapping_price}</span>
                        </span>
                    </p>
                </form><!-- /#klarnagift -->
            </div><!-- /.gift_container.kco-box -->
            {/if}
        </div><!-- /.col-xs-12.col-md-4 -->
        <div class="col-xs-12 col-md-8">
            <span class="kco-step-heading">{l s='Step 2' mod='klarnaofficial'}</span>
            <h4 class="kco-title kco-title--step">
                {l s='Pay for your order' mod='klarnaofficial'}
            </h4>
            <div id="checkoutdiv">{$klarna_checkout}</div>
        </div><!-- /#chcekoutdiv.col-xs-12.col-md-8 -->
    </div><!-- /.row -->
</div><!-- /#height_kco_div -->
{literal}
<script type="text/javascript">
    var klarna_opclink = '{/literal}{$klarna_update_cart_url|escape:'html':'UTF-8'}{literal}';
    $(document).ready(function(){ 
        $('.kco-trigger').each(function(){
            var el = $(this);
            var elTarget = el.next('.kco-target');
            el.click(function(){
                el.toggleClass('kco-trigger--inactive');
                elTarget.fadeToggle(150);
            });
        });
        $('.kco-sel-list__item').each(function(){
            var el = $(this);
            el.click(function(){
                el.siblings().removeClass('selected');
                el.addClass('selected');
            });
        });
        
        {/literal}{if $isv3}{literal}
        window._klarnaCheckout(function(api) {
          api.on({
            'shipping_address_change': function(data) {
                api.suspend();
                klarna_shipping_updated();
                api.resume();
            },
            'order_total_change': function(data) {
                api.suspend();
                klarna_shipping_updated();
                api.resume();
            }
          });
        });
        {/literal}{/if}{literal}
    });
</script>
{/literal}
{/if}
<div class="hideklarnaloaderbox" id="loaderbox">
    <span class="spinner"></span>
</div>