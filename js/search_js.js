$("#search_apply_filter").click(function () {
	var filter='';
	$("#filter_box option:selected").each(function(){
		filter += $(this).val()+'+';
	});
	filter = filter.slice(0,-1);
	call_search($("#search_field").val(),$("#search_in").val(),filter);
});
