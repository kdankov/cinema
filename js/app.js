// Functions
function de(data) {
  if(debug){ console.log(data); }
}

// Variables & Config
var debug = true;
var cache_url = '/cache/local/';
var movies_db_url = '/cache/movies/';
var months = ['Януари','Февруари','Март','Април','Май','Юни','Юли','Август','Септември','Октомври','Ноември','Декември'];
var dayoftheweek = [ 'Неделя', 'Понеделник', 'Вторник', 'Сряда', 'Четвъртък', 'Петък', 'Събота'];
var cinemas = ''; 
var updateStatusBar = navigator.userAgent.match(/iphone|ipad|ipod/i) &&
  parseInt(navigator.appVersion.match(/OS (\d)/)[1], 10) >= 7;

var traler = 'http://gdata.youtube.com/feeds/api/videos?q={title}-trailer&start-index=1&max-results=1&v=2&alt=json&hd'

// Functions
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

// When the HTML loads, may the fun begin!
$(function(){

  WebFontConfig = {
    google: {
      families: [ 
        'PT+Sans:400,700,400italic,700italic:cyrillic-ext,latin,latin-ext,cyrillic',
        'PT+Sans+Narrow:400,700:cyrillic-ext,latin,latin-ext,cyrillic',
        'Open+Sans:400italic,700italic,400,700:cyrillic-ext,latin,latin-ext,cyrillic',
        'Open+Sans+Condensed:300,300italic,700:cyrillic-ext,latin,latin-ext,cyrillic'
      ] 
    }
  };

  (function() {
    var wf = document.createElement('script');
    wf.src = ('https:' == document.location.protocol ? 'https' : 'http') + '://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
    wf.type = 'text/javascript';
    wf.async = 'true';
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(wf, s);
  })();

  $.getJSON('/js/cinemas.js', function(data){ 
    cinemas = data; 

    $.get('/js/templates/item.mst', function(template) {
      tplItem = template;
    });

    var today 	      = new Date(),
    weekdays	      = new Array(),

    $select_cinema    = $('#cinema'),
    $select_date      = $('#date'),
    $chedule	      = $('#schedule'), 

    dates             = GetDates(today, 6);

    function update_table(day, cinemaid, vtype){
      $chedule.html('');
      $.getJSON( cache_url + cinemaid + '-' + vtype + '-' + day + ".json", function( data ) {
        $chedule.append('<ul />');
        $.each( data, function(index, el){
          $chedule.find('ul').append( Mustache.render(tplItem, el) );
          $('.trailer a').magnificPopup({
            disableOn: 700,
            type: 'iframe',
            mainClass: 'mfp-fade',
            removalDelay: 160,
            preloader: false,
            fixedContentPos: false
          });
        });
      });
    }

    $.each( dates, function(index, d){
      $select_date.append('<option value="' + d.getFullYear() + '-' + ('0' + ( d.getMonth() + 1 ) ).slice(-2) + '-' + ( '0' + d.getDate() ).slice(-2) + '">' + dateFormat(d) + '</option>');
    });

    $.each( cinemas, function(index, item){
      $select_cinema.append('<option value="' + item.lid + '" data-vtype="' + item.vtype + '">' + item.cinema +'</option>');
    });

    $select_date.change(function(){ 
      update_table( $(this).val(), $("#cinema").find('option:selected').attr('value'), $("#cinema").find('option:selected').attr('data-vtype') ); 
    }).change();

    $select_cinema.change(function(){ 
      update_table( $("#date").val(), $(this).find('option:selected').attr('value'), $(this).find('option:selected').attr('data-vtype') ); 
      console.log($(this).attr('data-vtype'));
    });

  });
});
