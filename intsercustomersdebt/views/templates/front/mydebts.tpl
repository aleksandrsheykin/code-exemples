{*
* 2007-2015 PrestaShop
*
* IntserDiscountUser
*}
{*block name='head'}
    <link href="css/intsercss.css" rel="stylesheet" type="text/css"/>
{/block*}
<div>
    {capture name=path}
        <a href="{$link->getPageLink('my-account', true)|escape:'html'}">Моя учетная запись</a>
        <span class="navigation-pipe">{$navigationPipe}</span>
		Мои долги
    {/capture}
	<h2>Мои долги</h2>
	
	<div class="row">
		<div class="col-md-9">
			{if (isset($debt_list)) && (!empty($debt_list))}
				<div class="row text-right">
					<div class="col-md-2 text-center">
						<b>№ Заказa</b>
					</div>
					<div class="col-md-2 text-right">
						<b>Дата заказа</b>
					</div>					
					<div class="col-md-2">
						<b>Сумма заказа</b>
					</div>
					<div class="col-md-3">
						<b>Долг по заказу</b>
					</div>
					<div class="col-md-3">
						<b>Сумма просрочено</b>
					</div>
				</div>	
				<div class="panel-group" id="accordion">
					{foreach key=key item=debt_row from=$debt_list}
						<div class="panel panel-default">
							<div class="panel-heading">
								<h4 class="panel-title">
									<a data-toggle="collapse" data-parent="#accordion" href="#collapseOne{$key}">
										<div class="row text-right">
											<div class="col-md-2 text-center">
												<b>{$debt_row.ndoc}</b>
											</div>
											<div class="col-md-2 text-right">
												<b>{convertPrice price=$debt_row.date_doc}</b>
											</div>											
											<div class="col-md-2">
												<b>{convertPrice price=$debt_row.sum_order}</b>
											</div>
											<div class="col-md-3">
												<b>{convertPrice price=$debt_row.sum_debt}</b>
											</div>
											<div class="col-md-3">
												<b>{convertPrice price=$debt_row.sum_overdue}</b>
											</div>
										</div>
										<div class="row" style="margin-top: 10px;">
											<div class="col-md-12">
												<div class="progress">
													<div class="progress-bar progress-bar-success" style="padding-top:3px; width: {(($debt_row.sum_order - $debt_row.sum_debt) * 100) / $debt_row.sum_order}%">
														Оплачено {((($debt_row.sum_order - $debt_row.sum_debt) * 100) / $debt_row.sum_order)|round}%
													</div>
													<div class="progress-bar progress-bar-warning" style="padding-top:3px; width: {(($debt_row.sum_debt - $debt_row.sum_overdue) * 100) / $debt_row.sum_order}%">
														Долг {($debt_row.sum_debt * 100 / $debt_row.sum_order)|round}%
													</div>
													<div class="progress-bar progress-bar-danger" style="padding-top:3px; width: {($debt_row.sum_overdue * 100) / $debt_row.sum_order}%">
														Просрочено {(($debt_row.sum_overdue * 100) / $debt_row.sum_order)|round}%
													</div>
												</div>
											</div>
										</div>
									</a>
								</h4>								
							</div>
							<div id="collapseOne{$key}" class="panel-collapse collapse">
								<div class="panel-body">
									{if ((isset($payment_list)) && (!empty($payment_list.{$debt_row.id})))}
										<div class="row text-left">
											<div class="col-md-5">
												<span class="label label-primary">График платежей по заказу № {$debt_row.ndoc}</span>
											</div>
										</div>
										<div class="row">
											<div class="col-md-2">
												<b>Дата платежа</b>
											</div>
											<div class="col-md-3 text-right">
												<b>Сумма</b>
											</div>
										</div>
										{foreach key=key item=payment_row from=$payment_list.{$debt_row.id}}
											<div class="row">
												<div class="col-md-2">{$payment_row.date_payment}</div>
												<div class="col-md-3 text-right">{convertPrice price=$payment_row.sum_debt_unit}</div>
											</div>
										{/foreach}
									{else}
										График платежей отсутствует
									{/if}
								</div>
							</div>
						</div>
					{/foreach}
				</div>
			{else}
				<div class="alert alert-info" style="text-align: center;">Поздравляем! У Вас нет задолженностей по заказам :)</div>				
			{/if}
		</div>
	</div>
	{if ((isset($notspread_list)) && (!empty($notspread_list)))}
		<h2>Неразнесенные платежи</h2>
		<div class="row" style="padding-top: 10px;">
			<div class="col-md-9">		
				<div class="panel panel-default">
					<div class="panel-heading">
						<div class="row">
							<div class="col-md-2"><b>№ Платежа</b></div>
							<div class="col-md-2 text-right"><b>Сумма платежа</b></div>
							<div class="col-md-2 text-right"><b>Дата платежа</b></div>
							<div class="col-md-3 text-right"><b>Неразнесенная сумма</b></div>
						</div>
					</div>
					<div class="panel-body">
						{foreach key=key item=notspread_row from=$notspread_list}
							<div class="row">
								<div class="col-md-2">
									{$notspread_row.ndoc}
								</div>
								<div class="col-md-2 text-right">
									{convertPrice price=$notspread_row.sum_order}
								</div>
								<div class="col-md-2 text-right">
									{$notspread_row.date_payment}
								</div>
								<div class="col-md-3 text-right">
									{convertPrice price=$notspread_row.sum_notspread}
								</div>
							</div>
						{/foreach}
					</div>
				</div>
			</div>
		</div>		
	{/if}
</div>
