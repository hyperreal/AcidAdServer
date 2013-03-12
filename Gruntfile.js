module.exports = function(grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        less: {
            development: {
                options: {
                    paths: ["src/Hyper/AdsBundle/Resources/public/css"]
                },
                files: {
                    "src/Hyper/AdsBundle/Resources/public/css/main.css": "src/Hyper/AdsBundle/Resources/public/less/main.less"
                }
            },
            production: {
                options: {
                    paths: ["src/Hyper/AdsBundle/Resources/public/css"],
                    yuicompress: true
                },
                files: {
                    "src/Hyper/AdsBundle/Resources/public/css/main.min.css": "src/Hyper/AdsBundle/Resources/public/less/main.less"
                }
            }
        },
        watch: {
            scripts: {
                files: ['src/Hyper/AdsBundle/Resources/public/less/*.less'],
                tasks: ['less'],
                options: {
                    nospawn: true
                }
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.registerTask('default', ['less', 'watch']);
}