/**
 * Gruntfile.js Controlls
 *
 * @author Code Parrots <support@codeparrots.com>
 * @since  1.0.0
 */
module.exports = function(grunt) {

	'use strict';

	var pkg = grunt.file.readJSON( 'package.json' );

	grunt.initConfig({

		pkg: pkg,

		// js minification
		uglify: {
			scripts: {
				expand: true,
				cwd: 'lib/js/',
				src: [ '**/*.js', '!**/*.min.js' ],
				dest: 'lib/js/',
				ext: '.min.js'
			},
		},

		autoprefixer: {
			options: {
				browsers: [
					'Android >= 2.1',
					'Chrome >= 21',
					'Edge >= 12',
					'Explorer >= 7',
					'Firefox >= 17',
					'Opera >= 12.1',
					'Safari >= 6.0'
				],
				cascade: false
			},
			main: {
				src: [ 'lib/css/timeline-express-single-column.css' ]
			}
		},

		sass: {
			options: {
				precision: 5,
				sourceMap: false
			},
			main: {
				files: {
					'lib/css/timeline-express-single-column.css': '.dev/sass/style.scss'
				}
			}
		},

		cssmin: {
			options: {
				processImport: false,
				roundingPrecision: 5,
				shorthandCompacting: false
			},
			assets: {
				expand: true,
				cwd: 'lib/css/',
				src: [ '**/*.css', '!**/*.min.css' ],
				dest: 'lib/css/',
				ext: '.min.css'
			}
		},

		// Generate a nice banner for our css/js files
		usebanner: {
			taskName: {
				options: {
					position: 'top',
					replace: true,
					banner: '/*\n'+
						' * @Plugin <%= pkg.title %>\n' +
						' * @Author <%= pkg.author %>\n'+
						' * @Site <%= pkg.site %>\n'+
						' * @Version <%= pkg.version %>\n' +
						' * @Build <%= grunt.template.today("mm-dd-yyyy") %>\n'+
						' */',
					linebreak: true
				},
				files: {
					src: [
						'lib/css/*.min.css',
						'lib/js/*.min.js',
					]
				}
			}
		},

		watch: {
			public_css: {
				files: 'lib/css/*.css',
				tasks: [ 'sass', 'autoprefixer', 'cssmin' ],
				options: {
					spawn: false,
					event: [ 'all' ]
				},
			},
			public_js: {
				files: 'lib/js/*.js',
				tasks: [ 'uglify', 'usebanner' ],
				options: {
					spawn: false,
					event: [ 'all' ]
				},
			},
			sass: {
				files: '.dev/sass/**/*.scss',
				tasks: [ 'sass', 'autoprefixer', 'cssmin' ]
			}
		},

		replace: {
			base_file: {
				src: [ '<%= pkg.name %>.php' ],
				overwrite: true,
				replacements: [ {
					from: /Version: (.*)/,
					to: "Version: <%= pkg.version %>"
				} ]
			},
			readme_txt: {
				src: [ 'readme.txt' ],
				overwrite: true,
				replacements: [ {
					from: /Stable tag: (.*)/,
					to: "Stable tag: <%= pkg.version %>"
				} ]
			},
			readme_md: {
				src: [ 'README.md' ],
				overwrite: true,
				replacements: [ {
					from: /# <%= pkg.title %> v(.*)/,
					to: "# <%= pkg.title %> v<%= pkg.version %>"
				} ]
			},
			charset: {
				overwrite: true,
				replacements: [
					{
						from: /^@charset "UTF-8";\n/,
						to: ''
					}
				],
				src: [ 'lib/css/*.css' ]
			},
		},

		clean: {
			pre_build: [ 'build/*' ],
		},

		copy: {
			package: {
				files: [
					{
						expand: true,
						src: [
							'*.php',
							'*.txt',
							'lib/**'
						],
						dest: 'build/<%= pkg.name %>'
					}
				],
			}
		},

		compress: {
			main: {
				options: {
					archive: 'build/<%= pkg.name %>-v<%= pkg.version %>.zip'
				},
				files: [
					{
						cwd: 'build/<%= pkg.name %>/',
						dest: '<%= pkg.name %>/',
						src: [ '**' ]
					}
				]
			}
		},

		wp_deploy: {
			deploy: {
				options: {
					plugin_slug: '<%= pkg.name %>',
					build_dir: 'build/<%= pkg.name %>/',
					deploy_trunk: true,
					deploy_tag: pkg.version,
					max_buffer: 1024*1024*10
				}
			}
		}

	} );

	require( 'matchdep' ).filterDev( 'grunt-*' ).forEach( grunt.loadNpmTasks );

	grunt.registerTask( 'default', [
		'menu'
	] );

	grunt.registerTask( 'Development tasks.', [
			'sass',
			'autoprefixer',
			'uglify',
			'cssmin',
			'usebanner',
			'watch'
		]
	);

	// package release
	grunt.registerTask( 'Build the plugin.', [
			'replace',
			'clean:pre_build',
			'copy',
			'compress'
		]
	);

	grunt.registerTask( 'Deploy to WordPres.org.', [
			'copy',
			'wp_deploy'
		]
	);

};
