var gulp = require('gulp');
var browserSync = require('browser-sync').create();

var $ = require('gulp-load-plugins')();

var path = {
	SCSS_SRC	: './sass/**/*.scss',
	SCSS_DST	: './css',

	HTML_SRC	: './html/*.html',
	HTML_DST	: './'
}

gulp.task('sass', function () {

	gulp.src( path.SCSS_SRC )
		.pipe($.plumber({errorHandler: $.notify.onError("Error: <%= error.message %>")}))
		.pipe($.sourcemaps.init())
		.pipe($.sass())
		.pipe($.autoprefixer({ browsers: ['last 2 versions'], cascade: false }))
		.pipe($.csso())
		.pipe($.sourcemaps.write('map'))
		.pipe($.size({ showFiles: true }))
		.pipe(gulp.dest( path.SCSS_DST ))
		.pipe(browserSync.stream({ match: '**/*.css' }))
	;

});

gulp.task('html', function() {
	
	gulp.src( path.HTML_SRC )
		.pipe($.swig({defaults: { cache: false }}))
		.pipe($.size({ showFiles: true }))
		.pipe(gulp.dest( path.HTML_DST ))
	;
});

// Static Server + watching scss/html files
gulp.task('serve', ['sass'], function() {

	browserSync.init({
		server: {
			baseDir:	"./"
		}
	});

	gulp.watch( path.SCSS_SRC, ['sass']);
	gulp.watch( path.HTML_SRC, ['html']);

	gulp.watch( path.HTML_SRC ).on('change', browserSync.reload);

});

// Creating a default task
gulp.task('default', ['sass', 'html', 'serve']);

