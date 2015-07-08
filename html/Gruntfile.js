module.exports = function(grunt) {
    grunt.initConfig({
        bowercopy: {
            options: {
                // Bower components folder will be removed afterwards
                clean: true
            },
            // Anything can be copied
            css: {
                options: {
                    destPrefix: 'app/css'
                },

                files: {
                    'angular-csp.css': 'angular/angular-csp.css',
                    'ui-bootstrap-csp.css': 'angular-bootstrap/ui-bootstrap-csp.css',
                    'angular-material.css':'angular-material/angular-material.css',
                    'angular-material-icons.css': 'angular-material-icons/angular-material-icons.css',
                    'materialdesignicons.css': 'mdi/css/materialdesignicons.css'
                }
            },

            fonts: {
                options: {
                    destPrefix: 'app/css'
                },

                files: {
                    'MaterialIcons-Regular.eot':'material-design-icons/iconfont/MaterialIcons-Regular.eot',
                    'MaterialIcons-Regular.woff2': 'material-design-icons/iconfont/MaterialIcons-Regular.woff2',
                    'MaterialIcons-Regular.woff': 'material-design-icons/iconfont/MaterialIcons-Regular.woff',
                    'MaterialIcons-Regular.ttf': 'material-design-icons/iconfont/MaterialIcons-Regular.ttf'
                }
            },
            // Javascript
            libs: {
                options: {
                    destPrefix: 'app/scripts/libs'
                },
                files: {
                    'angular.js': 'angular/angular.js',
                    'ui-bootstrap.js': 'angular-bootstrap/ui-bootstrap.js',
                    'angular-material.js': 'angular-material/angular-material.js',
                    'angular-animate.js': 'angular-animate/angular-animate.js',
                    'angular-aria.js': 'angular-aria/angular-aria.js',
                    'angular-material-icons.js': 'angular-material-icons/angular-material-icons.js'
                }
            }
        }

    });

    grunt.loadNpmTasks('grunt-bowercopy');


};
