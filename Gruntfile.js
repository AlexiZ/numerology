module.exports = function(grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        uglify: {
            options: {
                separator: ";",
                banner: '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n'
            },
            common: {
                src: "web/compiled/common.js",
                dest: "web/compiled/common.min.js",
            },
            extranet: {
                src: "web/compiled/extranet.js",
                dest: "web/compiled/extranet.min.js",
            },
            site: {
                src: "web/compiled/site.js",
                dest: "web/compiled/site.min.js",
            },
        },
        cssmin: {
            options: {
                mergeIntoShorthands: false,
                roundingPrecision: -1
            },
            extranet: {
                files: {
                    'web/compiled/extranet.min.css': ['web/compiled/extranet.css']
                }
            },
            site: {
                files: {
                    'web/compiled/site.min.css': 'web/compiled/site.css'
                }
            },
        },
    });

    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');

    grunt.registerTask("default", ["uglify", "cssmin"]);
    grunt.registerTask("extranet", ["uglify:extranet", "cssmin:extranet"]);
    grunt.registerTask("site", ["uglify:site", "cssmin:site"]);
};
