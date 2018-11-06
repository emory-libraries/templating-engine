// Define global constants.
const SRC = 'src';
const DEST = 'public';

module.exports = function(grunt) {
  
  const path = require('path');
  
  grunt.initConfig({
    
    package: grunt.file.readJSON('package.json'),
    
    composer: grunt.file.readJSON('composer.json'),
    
    watch: {
      config: {
        files: ['Gruntfile.js', 'composer.json', 'package.json'],
        tasks: ['build'],
        options: {
          reload: true
        }
      }, 
      php: {
        files: [`${SRC}/php/**/*`],
        tasks: ['phplint', 'copy:php']
      },
      data: {
        files: [`${SRC}/data/**/*`, `${SRC}/meta/**/*`, `${SRC}/patterns/**/*`, `${SRC}/.htaccess`],
        tasks: ['copy:data']
      }, 
      engine: {
        files: [`${SRC}/*.{html,php}`],
        tasks: ['copy:engine']
      }
    },
    
    clean: {
      build: [`${DEST}/`]
    },
    
    copy: {
      php: {
        options: {
          mode: '0777'
        },
        files: [
          {expand: true, cwd: `${SRC}/`, src: ['php/**/*'], dest: `${DEST}/`},
          {expand: true, cwd: 'vendor/', src: ['**/*'], dest: `${DEST}/php/dependencies/`},
        ]
      },
      data: {
        options: {
          mode: '0777'
        },
        files: [
          {expand: true, cwd: `${SRC}/`, src: ['data/**/*'], dest: `${DEST}/`},
          {expand: true, cwd: `${SRC}/`, src: ['meta/**/*'], dest: `${DEST}/`},
          {expand: true, cwd: `${SRC}/`, src: ['patterns/**/*'], dest: `${DEST}/`},
          {expand: true, cwd: `${SRC}/`, src: ['*', '!*.{html,php}'], dest: `${DEST}/`, dot: true}
        ]
      },
      engine: {
        options: {
          mode: '0777'
        },
        files: [
          {expand: true, cwd: `${SRC}/`, src: ['*.{html,php}'], dest: `${DEST}/`}
        ]
      }
    },
    
    phplint: {
      php: [`${SRC}/php/**/*.php`]
    }
    
  });
  
  require('load-grunt-tasks')(grunt);
  
  grunt.registerTask('default', ['dev']);
  grunt.registerTask('build', [
    'clean',
    'copy'
  ]);
  grunt.registerTask('dev', [
    'build', 
    'watch'
  ]);
  grunt.registerTask('dist', [
    'build'
  ]);
  
};