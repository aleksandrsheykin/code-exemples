{*
* 2007-2015 PrestaShop
*
* IntserDiscountUser
*}

<div>
    {capture name=path}
        <a href="{$link->getPageLink('my-account', true)|escape:'html'}">Моя учетная запись</a>
        <span class="navigation-pipe">{$navigationPipe}</span>
		Мой список отслеживания
    {/capture}
	<h2>Мой список отслеживания</h2>
	{if $logged}	
		{if isset($product_list) && !empty($product_list)}
			<table>
			{foreach from=$product_list key=p_key item=p_row}
				<tr>
					<td><img src="{$p_row.image}" alt="{$p_row.product_name}" style="max-height: 125px;" /></td>
					<td>
						<a class="product-name" href="{$p_row.product_url|escape:'html':'UTF-8'}" title="{$p_row.product_name|escape:'html':'UTF-8'}" itemprop="url" >
							{$p_row.product_name|truncate:125:'...'|escape:'html':'UTF-8'}
						</a>
					</td>
					<td>
						<form method="POST" id="d_{$p_row.id_product}">
							<input type="hidden" name="pid" value="{$p_row.id_product}"/>
							<a href="#" onClick="document.getElementById('d_{$p_row.id_product}').submit();">удалить</a>
						</form>
					</td>
				</tr>
			{/foreach}
			</table>
		{else}
			<div class="row">
				<div class="col-md-9">
					<div class="alert alert-info" style="text-align: center;">
					Ваш список отслеживания пуст
					</div>
				</div>
			</div>
		{/if}
	{else}
		<div class="alert alert-warning">Для просмотра списка отслеживания необходимо 
			<a href="{$base_dir}login?back=my-account">авторизоваться</a>
		</div>
	{/if}
</div>
