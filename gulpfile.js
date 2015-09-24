var gulp = require('gulp');
var browserSync = require('browser-sync').create();

var $ = require('gulp-load-plugins')();

var path = {
		SCSS_SRC	: './sass/**/*.scss',
		SCSS_DST	: './css',

		JADE_SRC	: './source/jade/**/*.jade',
		JADE_DST	: './'
	}
	
gulp.task('sass', function () {
	gulp.src( path.SCSS_SRC )
    .pipe($.plumber({errorHandler: $.notify.onError("Error: <%= error.message %>")}))
	.pipe($.sourcemaps.init())
	.pipe($.sass())
	.pipe($.autoprefixer({ browsers: ['last 2 versions'], cascade: false }))
	.pipe($.csso())
	.pipe($.sourcemaps.write('map'))
	.pipe($.filesize())
	.pipe(gulp.dest( path.SCSS_DST ))
    .pipe(browserSync.stream({ match: 'css/**/*.css' }))
	//.pipe($.livereload())
	;
});

// Get and render all .jade files recursively 
gulp.task('jade', function () {
	gulp.src( path.JADE_SRC )
    .pipe($.plumber({errorHandler: $.notify.onError("Error: <%= error.message %>")}))
	.pipe($.jade())
    .pipe($.htmlPrettify({indent_char: ' ', indent_size: 4}))
	.pipe(gulp.dest( path.JADE_DST ))
	.pipe($.filesize())
	//.pipe($.livereload())
	;
});

gulp.task('watch', function() {
	$.livereload.listen();
	gulp.watch( path.SCSS_SRC, ['sass']);
});

// Static Server + watching scss/html files
gulp.task('serve', ['sass'], function() {

    browserSync.init({
        server: "./"
    });

	gulp.watch( path.SCSS_SRC, ['sass']);
    gulp.watch("css/*.css").on('change', browserSync.reload);
    gulp.watch("*.html").on('change', browserSync.reload);
});

// Creating a default task
gulp.task('default', ['sass', 'serve']);
