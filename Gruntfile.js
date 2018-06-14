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
        tasks: [],
        options: {
          reload: true
        }
      }, 
      php: {
        files: [`${SRC}/php/**/*`],
        tasks: ['phplint', 'copy:php']
      },
      data: {
        files: [`${SRC}/data/**/*`],
        tasks: ['copy:data']
      }
    },
    
    clean: {
      build: [`${DEST}/`]
    },
    
    copy: {
      php: {
        files: [
          {expand: true, cwd: `${SRC}/`, src: ['php/**/*'], dest: `${DEST}/`},
          {expand: true, cwd: 'vendor/', src: ['**/*', '!composer/**'], dest: `${DEST}/php/dependencies/`},
        ]
      },
      data: {
        files: [
          {expand: true, cwd: `${SRC}/`, src: ['data/**/*'], dest: `${DEST}/`}
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