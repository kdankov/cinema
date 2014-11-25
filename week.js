var fs = require('fs');
eval(fs.readFileSync('parser.js')+'');

var count = 0;

for( var weekday in weekdays ) {

	for( var cinema in c_ids ) {
		var url = 'http://cinemacity.bg/en/scheduleInfo?locationId=' + c_ids[cinema].lid + '&date=' + dates[count] + '&hideSite=1&venueTypeId=' + c_ids[cinema].vtype;
		var html = 'cache/cinemacity/' + c_ids[cinema].lid + '-' + c_ids[cinema].vtype + '-' + weekdays[weekday] + '.html';
		var json = 'cache/local/' + c_ids[cinema].lid + '-' + c_ids[cinema].vtype + '-' + weekdays[weekday] + '.json';

		parseCinemaCity( url, json, html );
	}

	count++;
}
