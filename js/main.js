// Variables & Config
var debug = true;
var cache_url = '../cache/';
var months = ['Януари','Февруари','Март','Април','Май','Юни','Юли','Август','Септември','Октомври','Ноември','Декември'];
var cinema = [['Mall Sofia', 'ms'], ['Paradise Center', 'pc']];
	
// Functions
function de(data) {
	if(debug){
		console.log(data);
	}
}

function update_table(day, cinema){
	$("#table").load(cache_url + cinema + '-' + day + ".html table", function(data) {
		de(day + ' ' + cinema);
	});	
}

// When the HTML loads, may the fun begins!
$(function(){

	var today = new Date();
	var weekdays = new Array();
	var yyyy = today.getFullYear();
	var mm = today.getMonth(); //January is 0!
	var dd = today.getDate();

	$('<header><h2>Програма за</h2></header>').prependTo("#jscontainer");

	$select_date = $('<select name="date" id="date" />');
	$select_cinema = $('<select name="cinema" id="cinema" />');

	for(var i=0; i<8; i++){

		var day = dd+i;
		var month = mm+1;
		if(day<10){day='0'+day} 
		if(mm<10){month='0'+month} 

		weekdays.push( yyyy + '-' + month + '-' + day );

		$select_date.append('<option value="' + yyyy + '-' + month + '-' + day + '">' + (dd+i) + ' ' + months[mm] +'</option>');
	}
	
	$select_date.change(function(){
		update_table( $(this).children('option:selected').attr('value'), cinema[0][1] );
	}).appendTo("#jscontainer > header")

	update_table(weekdays[0], cinema[0][1]);

	$("body")
		.delegate("td", "mouseover", function() {
			$(this).parents('table').find('td:nth-child(' + ($(this).index() + 1) + ')').addClass('highlight');
		})
		.delegate("td", "mouseout", function() {
			$(this).parents('table').find('td:nth-child(' + ($(this).index() + 1) + ')').removeClass('highlight');
		});
});


