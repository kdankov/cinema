// Variables & Config
var debug = true;
var cache_url = 'cache/cinemacity/weekly/';
var months = ['Януари','Февруари','Март','Април','Май','Юни','Юли','Август','Септември','Октомври','Ноември','Декември'];
var dayoftheweek = [ 'Неделя', 'Понеделник', 'Вторник', 'Сряда', 'Четвъртък', 'Петък', 'Събота'];
var cinema = [
	['Mall Sofia', '1010605'],
	['Mall Sofia (IMAX)', '1010605'],
	['Burgas', '1010601'],
	['Paradise Center', '1010602'],
	['Paradise Center (4DX)', '1010602'],
	['Plovdiv', '1010603'],
	['Rousse', '1010604'],
	['Stara Zagora', '1010606']
];

// Functions
function de(data) {
	if(debug){
		console.log(data);
	}
}

var updateStatusBar = navigator.userAgent.match(/iphone|ipad|ipod/i) &&
	parseInt(navigator.appVersion.match(/OS (\d)/)[1], 10) >= 7;

if (updateStatusBar) {
	$('body').addClass('ios7');
}

function dateFormat(currentDate){
	return dayoftheweek[currentDate.getDay()] + ", " + currentDate.getDate() + " " + months[currentDate.getMonth()]
}

function GetDates(startDate, daysToAdd) {
	var aryDates = [];

	for (var i = 0; i <= daysToAdd; i++) {
		var currentDate = new Date();
		currentDate.setDate(startDate.getDate() + i);
		aryDates.push(currentDate);
	}

	return aryDates;
}

// When the HTML loads, may the fun begins!
$(function(){

	var today		= new Date(),
		weekdays	= new Array(),

		$chedule	= $('#schedule'), 
		$select_date = $('#date'),
		$select_cinema = $('#cinema'),
		$cols		= []
	;

	var dates = GetDates(today, 7);
	//console.log(dates);

	function update_table(day, cinema)
	{
		$chedule.html('');

		$.getJSON( cache_url + cinema + '-' + day + ".json", function( data ) {

			de( data );

			de(day + ' ' + cinema);
			
			$chedule.append('<ul />');

			$.each( data, function(){
				
				de(this['screenings']);

				$chedule.find('ul').append(
					'<li>' + 
						'<span class="title">' + this['title-en'] + (this['3D'] ? '<b>3D</b>' : '') + '</span>' +
						'<span class="duration">' + this['duration'] + 'min </span>' +
					'</li>'
				);
			
			});

		});

		//$table.load(cache_url + cinema + '-' + day + ".html table", function(data) {
			//if( $table.find('td').size() > 0 )
			//{ 
				//$table.removeClass('noprogram').find('tr:gt(0)').each(function() // go through all rows (skip head row)
				//{
					//$(this).children().slice(1).each(function(i) // to through all cells (skip head cells) in the row and add them in the $cols array
					//{
						//if(!$cols[i]){ $cols[i] = []; }
						//$cols[i].push(this);
					//});
				//});
			//}
			//else
			//{
				//$table.addClass('noprogram').html('<h4>Няма програма за този ден</h4>');
			//}

			//de(day + ' ' + cinema);
		//});	
	}

	for(var i = 0, d; i < 7; i++){
		d = dates[i]
		$select_date.append('<option value="' + d.getFullYear() + '-' + ('0' + ( d.getMonth() + 1 ) ).slice(-2) + '-' + ( '0' + d.getDate() ).slice(-2) + '">' + dateFormat(dates[i]) + '</option>');
	}

	for(var i = 0; i < cinema.length; i++){
		$select_cinema.append('<option value="' + cinema[i][1] + '">' + cinema[i][0] +'</option>');
	}

	$select_date.change(function(){ update_table( $(this).val(), $("#cinema").val() ); }).change();
	$select_cinema.change(function(){ update_table( $("#date").val(), $(this).val() ); });

	var cols_highlight = function (index, shadowed)
	{
		if(index > 0)
		{
			for($c = $cols[index - 1], i = 0; i < $c.length; i++)
			{
				$c[i].className = (shadowed ? 'highlight' : '');
			}
		}
	};

	$table
		.delegate("td", "mouseover", function() { cols_highlight($(this).index(), true); })
		.delegate("td", "mouseout" , function() { cols_highlight($(this).index()); })
});
