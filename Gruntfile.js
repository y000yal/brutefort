/**
 * Gruntfile for BruteFort Plugin.
 *
 * @package BruteFort
 */

const fs = require( "fs" );

module.exports = function (grunt) {
	// Read .distignore for additional patterns to exclude from the zip.
	const distIgnorePatterns = fs.existsSync( ".distignore" )
	? fs
		.readFileSync( ".distignore", "utf-8" )
		.split( "\n" )
		.filter( (line) => line.trim() && ! line.startsWith( "#" ) )
		.map( (line) => ` ! ${line.trim()}` )
	: [];

	grunt.initConfig(
		{
			pkg: grunt.file.readJSON( "package.json" ),
			// Setting folder templates..
			dirs: {
				js: "assets/build",
				css: "assets/css"
			},
			// Clean the dist and release folders.
			clean: {
				build: ["dist", "release"],
			},

			// Minify admin.js to admin.min.js.
			uglify: {
				admin: {
					files: {
						"assets/build/admin.min.js": ["assets/build/admin.js"],
					},
				},
			},

			sass: {
				options: {
					sourceMap: false,
					implementation: require( "sass" ),
				},
				compile: {
					files: [
					{
						expand: true,
						cwd: "<%= dirs.css %>/",
						src: ["*.scss", "modules/**/*.scss"], // Include the modules directory.
						dest: "<%= dirs.css %>/",
						ext: ".css",
					},
					],
				},
			},
			// Minify all .css files..
			cssmin: {
				minify: {
					expand: true,
					cwd: "<%= dirs.css %>/",
					src: ["*.css"],
					dest: "<%= dirs.css %>/",
					ext: ".css",
				},
			},
			// Compress the plugin files directly from source into a zip.
			compress: {
				main: {
					options: {
						archive: "release/<%= pkg.name %>-<%= pkg.version %>.zip",
					},
					files: [
					{
						expand: true,
						cwd: ".",
						src: [
							"**",
							"!node_modules/**",
							"!dist/**",
							"!release/**",
							"!Gruntfile.js",
							"!package-lock.json",
							"!webpack.config.js",
							"!tests/**",
							"!composer.lock",
							"!phpcs.xml",
							"!changelog.txt",
							"!src/**",
							"!scripts/**",
							"!*.config.js",
							"!*.config.ts",
							"!*.babelrc",
							"!*.eslintrc.js",
							"!*.prettierrc",
							"!*.md",
							"!.vscode/**",
							"!.idea/**",
							"!*.swp",
							"!*.swo",
							"!.DS_Store",
							"!Thumbs.db",
							"!package.json",
							"!package-lock.json",
							"!tsconfig.json",
							"!tailwind.config.js",
							"!postcss.config.js",
							"!webpack.config.js",
							"!Gruntfile.js",
							"!src/**",
							"!scripts/**",
							"!tests/**",
							"!node_modules/**",
							"!dist/**",
							"!release/**",
							"!*.zip",
							"!.babelrc",
							"!.eslintrc.js",
							"!.prettierrc",
							"!*.config.js",
							"!*.config.ts",
							"!changelog.txt",
							"!phpcs.xml",
							"!README.md",
							"!CHANGELOG.md",
							"!deploy.sh",
							"!test-deploy.sh"
						],
						dest: "brutefort/"
					},
					],
				},
			},
			shell: {
				composerProd: {
					command: "composer install --no-dev --optimize-autoloader",
				},
				composerDev: {
					command: "composer install",
				},
				build: {
					command: "cross-env NODE_ENV=production webpack"
				},
				makepot: {
					command: "vendor\\bin\\wp i18n make-pot . languages\\brutefort.pot --domain=brutefort"
				},
				phpcs: {
					command: "npm run phpcs"
				},
				phpcsFix: {
					command: "npm run phpcs:fix"
				}
			},
		}
	);

	// Load plugins.
	grunt.loadNpmTasks( "grunt-contrib-clean" );
	grunt.loadNpmTasks( "grunt-contrib-compress" );
	grunt.loadNpmTasks( "grunt-contrib-uglify" );
	grunt.loadNpmTasks( "grunt-sass" );
	grunt.loadNpmTasks( "grunt-contrib-cssmin" );
	grunt.loadNpmTasks( "grunt-shell" );

	grunt.registerTask(
		"release",
		[
		"clean:build",
		"shell:phpcsFix",
		"shell:phpcs",
		"shell:build",
		"uglify",
		"sass",
		"cssmin",
		"shell:makepot",
		"shell:composerProd",
		"compress",
		"shell:composerDev",
		]
	);

	// Pre-commit task for code quality checks.
	grunt.registerTask(
		"precommit",
		[
		"shell:phpcs",
		]
	);

	// Code quality check task.
	grunt.registerTask(
		"quality",
		[
		"shell:phpcs",
		]
	);
};
