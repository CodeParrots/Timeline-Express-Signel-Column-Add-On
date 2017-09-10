/**
 * Gruntfile.js Controlls
 *
 * @author Code Parrots <support@codeparrots.com>
 * @since  1.0.0
 */
module.exports = function(grunt) {

	'use strict';

	var pkg = grunt.file.readJSON( 'package.json' );

	grunt.initConfig( {

		pkg: pkg,

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
				files: [ 'lib/css/*.css', '! lib/css/*.min.css' ],
				tasks: [ 'autoprefixer', 'cssmin', 'usebanner' ],
				options: {
					spawn: false,
					event: [ 'all' ]
				},
			},
			public_js: {
				files: [ 'lib/js/*.js', '!lib/js/*.min.js' ],
				tasks: [ 'uglify', 'usebanner' ],
				options: {
					spawn: false,
					event: [ 'all' ]
				},
			},
			images: {
				files: [
					'assets/images/**/*.{gif,jpeg,jpg,png,svg}',
					'wp-org-assets/**/*.{gif,jpeg,jpg,png,svg}'
				],
				tasks: [ 'imagemin' ]
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
					assets_dir: 'wp-org-assets/',
					plugin_slug: '<%= pkg.name %>',
					build_dir: 'build/<%= pkg.name %>/',
					deploy_trunk: true,
					deploy_tag: pkg.version,
					max_buffer: 1024*1024*10
				}
			}
		},

		imagemin: {
			options: {
				optimizationLevel: 3
			},
			wp_org_assets: {
				expand: true,
				cwd: 'wp-org-assets/',
				src: [ '**/*.{gif,jpeg,jpg,png,svg}' ],
				dest: 'wp-org-assets/'
			}
		},

		wp_readme_to_markdown: {
			options: {
				post_convert: function( readme ) {

					var matches = readme.match( /\*\*Tags:\*\*(.*)\r?\n/ ),
					    tags    = matches[1].trim().split( ', ' ),
					    section = matches[0];

					for ( var i = 0; i < tags.length; i++ ) {

						section = section.replace( tags[i], '[' + tags[i] + '](https://wordpress.org/themes/tags/' + tags[i] + '/)' );

					}

					// Banner
					if ( grunt.file.exists( 'wp-org-assets/banner-772x250.jpg' ) ) {

						readme = readme.replace( '**Contributors:**', "![Banner Image](wp-org-assets/banner-772x250.jpg)\r\n\r\n**Contributors:**" );

					}

					// Tag links
					readme = readme.replace( matches[0], section );

					// Badges
					readme = readme.replace( '## Description ##', grunt.template.process( pkg.badges.join( ' ' ) ) + "  \r\n\r\n## Description ##" );

					return readme;

				}
			},
			main: {
				files: {
					'readme.md': 'readme.txt'
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
		'replace',
		'autoprefixer',
		'cssmin',
		'uglify',
		'usebanner',
		'imagemin',
		'wp_readme_to_markdown'
	] );

	grunt.registerTask( 'Build the plugin.', [
		'Development tasks.',
		'clean:pre_build',
		'copy',
		'compress'
	] );

	grunt.registerTask( 'Deploy to WordPres.org.', [
		'Build the plugin.',
		'wp_deploy'
	] );

	grunt.registerTask( 'Generate readme.', [
		'wp_readme_to_markdown'
	] );

	grunt.registerTask( 'Replace versions.', [
		'replace'
	] );

};
