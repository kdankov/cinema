var fs = require('fs');
eval(fs.readFileSync('parser.js') + '');

function parseKinoArena( url, json, htmlpath ){
	
	request(url, function(error, response, html){
		if(!error){

			var $ = cheerio.load(html);
			var movies = [];

			$('div.scheduleRow').each(function(i, elem){

				var data = $(this);

				var movieName   = data.find('h5.title').text();
				//var rating		= data.next().text();
				//var type        = data.next().next().text();
				//var language	= data.next().next().next().text();
				//var duration    = data.next().next().next().next().text();

				var poster   = data.find('div.movieBox img').attr('src');

				// grab_image( poster, movieinfo_poster );
				
				var screenings	= [];

				var is2D   = false;
				var is3D   = false;
				var isA    = false;
				var isB    = false;
				var isC    = false;
				var isD    = false;
				var isIMAX = false;
				var is4DX  = false;


				var synopsis = '';

				data.find('div.timeTable a.booking').each(function(i, elem){

					var time = $(elem).text();
					var url = $(elem).attr('href');

					time = time.replace("\n", '', 'gi');
					time = time.replace("\n", '', 'gi');
					time = time.replace(/ ?/gi, '');

					var screening = {
						'time' : time,
						'url' : url
					}

					screenings.push(screening);

				});

				var movie = {
					'title'         : movieName,
					'poster'        : poster,
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


var url = 'http://localhost.com:8000/ARENADELUXE.html'
var html = 'cache/kinoarena/ARENADELUXE.html';
var json = 'cache/kinoarena/local/ARENADELUXE.json';

parseKinoArena( url, json, html );

