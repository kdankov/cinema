var fs = require('fs');
eval(fs.readFileSync('parser.js') + '');

for( var cinema in c_ids )
{

	var url = 'http://cinemacity.bg/en/scheduleInfo?locationId=' + c_ids[cinema].lid + '&date=' + dates[0] + '&hideSite=1&venueTypeId=' + c_ids[cinema].vtype;
	var html = 'cache/cinemacity/' + c_ids[cinema].lid + '-' + c_ids[cinema].vtype + '-' + weekdays[0] + '.html';
	var json = 'cache/local/' + c_ids[cinema].lid + '-' + c_ids[cinema].vtype + '-' + weekdays[0] + '.json';

	parseCinemaCity( url, json, html );
}
