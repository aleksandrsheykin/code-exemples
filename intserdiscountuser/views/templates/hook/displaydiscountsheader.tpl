{*
* 2007-2015 PrestaShop
*
* IntserDiscountUser
*}

<!-- MODULE IntserDiscount -->
{assign var='intser_heigh' value=$intser_countrow*38}
<div class='header_user_info'>
	<a href="{$link->getModuleLink('intserdiscountuser', 'mydiscounts', array(), true)|escape:'html':'UTF-8'}" onmouseover='IntserShow("IntserBlockDiscount",{$intser_heigh},6)' onmouseout='IntserShow("IntserBlockDiscount",{$intser_heigh},6)'>
	Мои скидки
	</a>
</div>
<div id='IntserBlockDiscount' class='intserdiscountheader' style='height: {$intser_heigh}px;' onmouseover='IntserShow("IntserBlockDiscount",{$intser_heigh},6)' onmouseout='IntserShow("IntserBlockDiscount",{$intser_heigh},6)'>
	{if isset($discount_list)}
		<table class="intserdiscounttable">
			{foreach key=key item=discount_row from=$discount_list}
			<tr>
				<td>{$discount_row.cat_name}</td>
				<td>
					{if $discount_row.discount_cart > $discount_row.discount_individual}
						<span class="old-price" style="color: white;">{$discount_row.discount_individual}%</span>
						{$discount_row.discount_cart}%
					{else}
						{$discount_row.discount_individual}%
					{/if}
				
				</td>
			</tr>
			{/foreach}
		</table>
	{else}	
		<table class="intserdiscounttable">
			<tr><td>У Вас нет накопительных скидок</td></tr>
		</table>
	{/if}
</div>
<!-- END : MODULE IntserDiscount -->