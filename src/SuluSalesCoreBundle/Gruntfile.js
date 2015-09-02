module.exports = function(grunt) {
    var min = {},
        bundleName = 'sulusalescore',
        path = require('path'),
        srcpath = 'Resources/public/js',
        destpath = 'Resources/public/dist';

    // Build config "min" object dynamically.
    grunt.file.expand({cwd: srcpath}, '**/*.js').forEach(function(relpath) {
        // Create a target Using the verbose "target: {src: src, dest: dest}" format.
        min[relpath] = {
            src: path.join(srcpath, relpath),
            dest: path.join(destpath, relpath)
        };
        // The more compact "dest: src" format would work as well.
        // min[path.join(destpath, relpath)] = path.join(srcpath, relpath);
    });

    // load all grunt tasks
    require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks);

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        copy: {
            templates: {
                files: [
                    {expand: true, cwd: srcpath, src: ['**/*.html'], dest: destpath}
                ]
            },
            hooks: {
                files: [
                    {
                        expand: true,
                        flatten: true,
                        src: [
                            'bin/hooks/*'
                        ],
                        dest: '.git/hooks/'
                    }
                ]
            }
        },

        exec: {
            hookrights: {
                command: 'chmod +x .git/hooks/pre-push'
            }
        },

        clean: {
            options: { force: true },
            hooks: ['.git/hooks/*']
        },

        watch: {
            options: {
                nospawn: true
            },
            compass: {
                files: ['Resources/public/scss/{,*/}*.{scss,sass}'],
                tasks: ['compass:dev']
            },
            scripts: {
                files: ['Resources/public/**'],
                tasks: ['publish']
            }
        },
        jshint: {
            options: {
                jshintrc: '.jshintrc'
            }
        },
        cssmin: {
            // TODO: options: { banner: '<%= meta.banner %>' },
            compress: {
                files: {
                    'Resources/public/css/main.min.css': ['Resources/public/css/main.css']
                }
            }
        },
        compass: {
            dev: {
                options: {
                    sassDir: 'Resources/public/scss/',
                    specify: [
                        'Resources/public/scss/main.scss',
                        'Resources/public/scss/pdfstyles.scss'
                    ],
                    cssDir: 'Resources/public/css/',
                    relativeAssets: false
                }
            }
        },
        uglify: min,
        replace: {
            build: {
                options: {
                    variables: {
                        'sulusalescore/js': bundleName+'/dist'
                    },
                    prefix: ''
                },
                files: [
                    {src: [destpath + '/main.js'], dest: destpath + '/main.js'}
                ]
            }
        }
    });

    grunt.registerTask('build', [
        'uglify',
        'replace:build',
        'copy:templates',
        'compass:dev',
        'cssmin'
    ]);

    grunt.registerTask('default', [
        'watch'
    ]);

    grunt.registerTask('install:hooks', [
        'clean:hooks',
        'copy:hooks',
        'exec:hookrights'
    ]);
};
