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
    });

    grunt.loadNpmTasks('grunt-contrib-uglify');

    grunt.registerTask("default", ["uglify"]);
};
