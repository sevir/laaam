$(function(){
	$(".enable_change").change(function(){
		if ($(this).is(':checked')){
			$("input:first",$(this).parent().parent()).removeAttr("disabled");
		}else{
			$("input:first",$(this).parent().parent()).attr("disabled","disabled");
		}
	});
});