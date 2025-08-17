/* jshint node:true */
const fs = require("fs");

module.exports = function (grunt) {
  // Read .distignore for additional patterns to exclude from the zip
  const distIgnorePatterns = fs.existsSync(".distignore")
    ? fs
        .readFileSync(".distignore", "utf-8")
        .split("\n")
        .filter((line) => line.trim() && !line.startsWith("#"))
        .map((line) => `!${line.trim()}`)
    : [];

  grunt.initConfig({
    pkg: grunt.file.readJSON("package.json"),
    // Setting folder templates.
		dirs: {
			js: "assets/build",
			css: "assets/css"
		},  
    // Clean the dist and release folders
    clean: {
      build: ["dist", "release"],
    },

    // Minify admin.js to admin.min.js
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
        implementation: require("sass"),
      },
      compile: {
        files: [
          {
            expand: true,
            cwd: "<%= dirs.css %>/",
            src: ["*.scss", "modules/**/*.scss"], // Include the modules directory
            dest: "<%= dirs.css %>/",
            ext: ".css",
          },
        ],
      },
    },
    // Minify all .css files.
    cssmin: {
      minify: {
        expand: true,
        cwd: "<%= dirs.css %>/",
        src: ["*.css"],
        dest: "<%= dirs.css %>/",
        ext: ".css",
      },
    },
    // Copy all plugin files except excluded ones to dist
    copy: {
      main: {
        files: [
          {
            expand: true,
            src: [
              "**",
              "!node_modules/**",
              "!dist/**",
              "!release/**",
              "!Gruntfile.js",
              "!package-lock.json",
              "!webpack.config.js",
              "!tests/**",
              "!composer.*",
              "!phpcs.xml",
              "!changelog.txt",
              ...distIgnorePatterns,
            ],
            dest: "dist/",
          },
        ],
      },
    },

    // Compress the dist folder into a zip
    compress: {
      main: {
        options: {
          archive: "release/<%= pkg.name %>-<%= pkg.version %>.zip",
        },
        files: [
          { expand: true, cwd: "dist/", src: ["**"], dest: "brutefort/" },
        ],
      },
    },
    shell: {
      composerProd: {
        command: "composer install --no-dev --optimize-autoloader",
      },
      build: {
        command: "cross-env NODE_ENV=production webpack"
      }
    },
  });

  // Load plugins
  grunt.loadNpmTasks("grunt-contrib-clean");
  grunt.loadNpmTasks("grunt-contrib-copy");
  grunt.loadNpmTasks("grunt-contrib-compress");
  grunt.loadNpmTasks("grunt-contrib-uglify");
  grunt.loadNpmTasks("grunt-sass");
  grunt.loadNpmTasks("grunt-contrib-cssmin");
  grunt.loadNpmTasks("grunt-shell");
  // Default task(s).
  grunt.registerTask("default", [
    "clean",
    "uglify",
    "sass",
    "cssmin",
    "copy",
    "compress",
  ]);
  grunt.registerTask("release", [
    "clean",
    "shell:build",
    "uglify",
    "sass",
    "cssmin",
    "copy",
    "compress",
  ]);
};
