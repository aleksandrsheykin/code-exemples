<script>
	function SendPost(idform) {
		document.getElementById(idform).submit(); 
		return false;
	}	
</script>

<div class="col-lg-6">
	<div class="panel col-lg-12">
		<div class="panel-heading">
			Скидки по категориям
			<span class="badge">5</span>
			<span class="panel-heading-action">
				<a class="list-toolbar-btn" href="javascript:location.reload();">
					<span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='Refresh list'}" data-html="true" data-placement="top">
						<i class="process-icon-refresh"></i>
					</span>
				</a>
			</span>
		</div>
		
		<div class="panel col-lg-12">
			<div class="row">
				<form method="POST" id="form_add">				
					<div class="col-md-5">
						<select name="insert_selectedcat" id="insert_selectedcat">
							<option value="0">-</option>				
							{foreach key=key item=skidk from=$skidka}
								<option value="{$skidk.cat_id}" >{$skidk.cat_name}</option>
							{/foreach}
						</select>
					</div>
					<div class="col-md-5">
						<input type="text" name="insert_discountval" id="insert_discountval" placeholder="Скидка" />
					</div>
					<div class="col-md-2">
						<a id="IntserCategoryAdd" name="IntserCategoryAdd" class="list-toolbar-btn" href="javascript:void(0);" OnClick="SendPost('form_add');">
							<span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Добавить" data-html="true" data-placement="top">
								<i class="process-icon-new"></i>
							</span>
						</a>
					</div>
				</form>
			</div>
		</div>
		{if isset($msg_err)}
			<div class="row">
				<div class="col-md-12">
					<div class="alert alert-danger" role="alert">
						{$msg_err}
					</div>
				</div>
			</div>
		{/if}
		
		{strip}
		{/strip}
		<table class="table">
			<thead>
				<tr>
					<th><span class="title_box">id</span></th>
					<th><span class="title_box">Категория</span></th>
					<th><span class="title_box">Скидка</span></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				{foreach key=key item=customer_row from=$customer_list}
				<tr class="alt_row">
					<td>{$customer_row.cat_id}</td>
					<td>{$customer_row.cat_name}</td>
					<td>{$customer_row.discount}</td>
					<td>
						<form method="POST" id="form_del_{$customer_row.cat_id}">
							<input type="hidden" name="id_del" value="{$customer_row.cat_id}">
							<input type="submit" style="display: none;" id="btn_del_{$customer_row.cat_id}">
						</form>
						<a href="javascript:void(0);" title="Удалить" class="delete" OnClick="SendPost('form_del_{$customer_row.cat_id}');"><i class="icon-trash"></i> Удалить</a>
					</td>
				</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
</div>
