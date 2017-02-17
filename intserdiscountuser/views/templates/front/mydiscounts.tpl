{*
* 2007-2015 PrestaShop
*
* IntserDiscountUser
*}

<div>
    {capture name=path}
        <a href="{$link->getPageLink('my-account', true)|escape:'html'}">Моя учетная запись</a>
        <span class="navigation-pipe">{$navigationPipe}</span>
		Мои скидки
    {/capture}
	<h2>Мои скидки</h2>
	
	<div class="row">
		<div class="col-md-6">	
			{if isset($discount_list)}
				<table class="table">
					<thead>
						<tr>
							<th style="width: 40%;"><span class="title_box">Категория</span></th>
							<th><span class="title_box">Скидка (%)</span></th>
						</tr>
					</thead>
					<tbody>
						{foreach key=key item=discount_row from=$discount_list}
							<tr>
								<td>{$discount_row.cat_name}</td>
								<td>
									{if $discount_row.discount_cart > $discount_row.discount_individual}
										<span class="old-price">{$discount_row.discount_individual}%</span>
										{$discount_row.discount_cart}% 
										<a href="{$link->getCMSLink('3')}">(скидка от суммы заказа)</a>
									{else}
										{$discount_row.discount_individual}%
									{/if}
								
								</td>
							</tr>
						{/foreach}						
					</tbody>
				</table>
			{else}	
				У Вас нет накопительных скидок
			{/if}
		</div>
	</div>
</div>
