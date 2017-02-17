function IntserAddProduct(id_product)
{
	//alert('asdf');
	$.ajax({
		type: 'GET',
		url: baseDir + 'modules/intseralertintake/addproduct.php?rand=' + new Date().getTime(),
		headers: { "cache-control": "no-cache" },
		async: true,
		cache: false,
		data: 'id_product=' + id_product,
		success: function(data)
		{
			$.fancybox.open([
				{
					type: 'inline',
					autoScale: true,
					minHeight: 30,
					content: '<p class="fancybox-error">'+data+'</p>'
				}
			], {
				padding: 0
			});
		}
	});
}