$(function(){

	$('.select .option').click(function(event){
		$(this).siblings().removeClass('selected').end().addClass('selected').parent().addClass('has-selection');
		event.preventDefault();
	});

	$('abbr[title]').each(function(){
		$(this).attr('data-title', $(this).attr('title')).attr('title', '');
	});
	
});

