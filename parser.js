var request        = require('request');
var cheerio        = require('cheerio');

var date           = require('./phpdate.js');
var strtotime      = require('./strtotime.js');
var c_ids		   = require('./cinemas.json');

var weekdays       = new Array();
var dates          = new Array();

var trailerspath   = 'cache/movies/trailers/';

for(var i=0; i<7; i++) {
	weekdays[i] = date.date("Y") + "-" + date.date("m") + "-" + date.date("d", strtotime.strtotime('+' + i + ' day'));
}

for(var i=0; i<7; i++){
	dates[i] = date.date("d",strtotime.strtotime('+' + i + ' day')) + "/" + date.date("m") + "/" + date.date("Y");
}

function grab_image( url, saveto ) {
	if ( !fs.existsSync(saveto) ) {
		request( url ).pipe(fs.createWriteStream( saveto ));
	}
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

				var movieinfo_url      = 'http://cinemacity.bg/en/featureInfo?featureCode=' + featureCode;
				var movieinfo_html     = 'cache/movies/' + featureCode + '.html';
				var movieinfo_poster   = 'cache/movies/posters/' + featureCode + '.jpg';

				var movietrailer_url = 'http://gdata.youtube.com/feeds/api/videos?q=' + movieName.replace(' ', '+') + '-trailer&start-index=1&max-results=1&v=2&alt=json&hd';
				var movietrailer_json = '';
				var movietrailer_link = '';

				var poster = '';
				var synopsis = '';

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

				});

				request(movietrailer_url, function(t_error, t_response, t_html){
					if(!error){

						movietrailer_json = JSON.parse(t_html);

						if ( movietrailer_json['feed']['entry'] != undefined ) {

							var trailerLink = movietrailer_json['feed']['entry'][0]['link'][0]['href'];
							var trailerLink = trailerLink.split('?v=')[1].split('&')[0];
							var trailerLink = 'http://www.youtube.com/watch?v=' + trailerLink;

							movietrailer_link = trailerLink;

							trailer = {
								name : movieName,
								link : trailerLink
							}

							var specialName = movieName.toLowerCase();
							sepcialName = specialName.split(' ').join('_');
							var path = trailerspath + sepcialName + '.json';

							//fs.writeFile( path , JSON.stringify(trailer, null, 4), function(err){
							//console.log('File successfully written! ' + path );
							//})

						}
					}
				});

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

				var movie = {
					'title'         : movieName,
					'poster'        : poster,
					'feature-code'  : featureCode,
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
			console.log('File successfully written! ' + json );
		})

	})

}

