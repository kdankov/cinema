var express = require('express');
var fs = require('fs');
var request = require('request');
var cheerio = require('cheerio');
var date = require('./phpdate.js');
var strtotime = require('./strtotime.js');
var app = express();

var cinemacity_ids = require('./cinemas.json');

var weekdays = new Array();
var dates = new Array();

for(var i=0; i<7; i++) {
  weekdays[i] = date.date("Y") + "-" + date.date("m") + "-" + date.date("d", strtotime.strtotime('+' + i + ' day'));
}

for(var i=0; i<7; i++){
  dates[i] = date.date("d",strtotime.strtotime('+' + i + ' day')) + "/" + date.date("m") + "/" + date.date("Y");
}

function grab_image( url, saveto ) {
	request( url ).pipe(fs.createWriteStream( saveto ));
};

function parseCinemaCity( url, json, htmlpath ){

	request(url, function(error, response, html){
		if(!error){

			var $ = cheerio.load(html);
			var movies = [];


			$('td.featureName').each(function(i, elem){
				var data = $(this);

				var movieName   = data.children().first().text();
				var featureCode = data.children().first().attr("data-feature_code");
				var rating		= data.next().text();
				var type        = data.next().next().text();
				var language	= data.next().next().next().text();
				var duration    = data.next().next().next().next().text();

				var screenings	= [];

				var is2D   = false;
				var is3D   = false;
				var isA    = false;
				var isB    = false;
				var isC    = false;
				var isD    = false;
				var isIMAX = false;
				var is4DX  = false;

				data.parent().find('td.prsnt a').each(function(i, elem){

					var time = $(elem).text();

					time = time.replace("IMAX", '');
					time = time.replace("4DX", '')
					time = time.replace("<br/>", '')
					time = time.replace("\r\n", '', 'gi');
					time = time.replace("\r\n", '', 'gi');
					time = time.replace(" ", '');

					var screening = {
						'time' : time,
						'code' : $(elem).attr('data-prsnt_code')
					}

					screenings.push(screening);

					//console.log( JSON.stringify(screenings, null, 4) );
					//console.log( typeof screenings );
					//console.log( '\n\n' );
					

				});

				var movieinfo_url      = 'http://cinemacity.bg/en/featureInfo?featureCode=' + featureCode;
			    var movieinfo_html     = 'cache/movies/' + featureCode + '.html';
			    var movieinfo_poster   = 'cache/movies/posters/' + featureCode + '.jpg';

				var poster = '';
				var synopsis = '';

				request(movieinfo_url, function(error, response, html){
					if(!error){
						var $a = cheerio.load(html);
						
						poster = $a('div.poster_holder img').attr('src'); 
						synopsis = $a('div.feature_info_synopsis').text(); 
						
						grab_image( poster, movieinfo_poster );
					}
				})

				movieName = movieName.replace(" (A)", "");
				movieName = movieName.replace(" (B)", "");
				movieName = movieName.replace(" (С)", "");
				movieName = movieName.replace(" (D)", "");
				movieName = movieName.replace(" 4DX", "");
				movieName = movieName.replace(" 2D", "");
				movieName = movieName.replace(" 3D", "");
				movieName = movieName.replace(" IMAX", "");

				//console.log('\n');

				var movie = {
					'title'         : movieName,
					'poster'        : poster,
					'feature-code'  : featureCode,
					//'trailer'       : movietrailer_link,
					'language'      : language,
					'rating'        : rating,
					'duration'      : duration,
					'type'          : type,
					'3D'            : is3D,
					'IMAX'          : isIMAX,
					'4DX'           : is4DX,
					'A'             : isA,
					'B'             : isB,
					'C'             : isC,
					'D'             : isD,
					'synopsis'      : synopsis,
					'screenings'    : screenings
				}
				
				movies.push(movie);
			})

		}

		fs.writeFile( json, JSON.stringify(movies, null, 4), function(err){
			console.log('File successfully written!');
		})

	})

}

app.get('/today', function(req, res){

	for( var cinema in cinemacity_ids ) {
	
		url = 'http://cinemacity.bg/en/scheduleInfo?locationId=' + cinemacity_ids[cinema].lid + '&date=' + dates[0] + '&hideSite=1&venueTypeId=' + cinemacity_ids[cinema].vtype;
		html = 'cache/cinemacity/' + cinemacity_ids[cinema].lid + '-' + cinemacity_ids[cinema].vtype + '-' + weekdays[0] + '.html';
		json = 'cache/local/' + cinemacity_ids[cinema].lid + '-' + cinemacity_ids[cinema].vtype + '-' + weekdays[0] + '.json';

		parseCinemaCity( url, json, html );
		//console.log( url, json, html );
	}

	res.send('Check your console!')

});

app.get('/week', function(req, res){
	
	var count = 0;

	for( var weekday in weekdays ) {

		for( var cinema in cinemacity_ids ) {
			var url = 'http://cinemacity.bg/en/scheduleInfo?locationId=' + cinemacity_ids[cinema].lid + '&date=' + dates[count] + '&hideSite=1&venueTypeId=' + cinemacity_ids[cinema].vtype;
			var html = 'cache/cinemacity/' + cinemacity_ids[cinema].lid + '-' + cinemacity_ids[cinema].vtype + '-' + weekdays[weekday] + '.html';
			var json = 'cache/local/' + cinemacity_ids[cinema].lid + '-' + cinemacity_ids[cinema].vtype + '-' + weekdays[weekday] + '.json';

			parseCinemaCity( url, json, html );
			//console.log( url, json, html );
		}

		count++;
	}

	res.send('Check your console!')
	
});

app.listen('8081')
console.log('Magic happens on port 8081');
exports = module.exports = app; 	
