'use strict';
module.exports = function (grunt) {
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		sass: {
			dist: {
				options: {
					outputStyle: 'compressed',
					includePaths: require('node-bourbon').includePaths
				},
				files: {
					'css/app.css': 'sass/app.scss'
				},
			},
			sourceMap: {
				options: {
					sourceComments: 'map',
					sourceMap: 'app.css.map',
					includePaths: require('node-bourbon').includePaths
				},
				files: {
					'css/app.css': 'sass/app.scss'
				}
			},
		},
		watch: {
			scripts: {
				files: ['sass/*.scss'],
				tasks: ['sass'],
			},
			livereload: {
				files: ['*.html', '*.php', 'js/**/*.{js,json}', 'css/*.css','img/**/*.{png,jpg,jpeg,gif,webp,svg}'],
				options: {
					livereload: true
				}
			}
		},
	});

	grunt.registerTask('default', ['sass', 'watch']);

	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-sass');
};
