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
{extends $layout}

{block name='content'}

{if isset($klarna_error)}
<div class="alert alert-warning">{$klarna_error|escape:'html':'UTF-8'}</div>
{else}
{$klarna_html nofilter}
{/if}
{if isset($HOOK_ORDER_CONFIRMATION)}
{$HOOK_ORDER_CONFIRMATION nofilter}
{/if}
{/block}