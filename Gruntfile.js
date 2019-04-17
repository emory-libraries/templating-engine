module.exports = function(grunt) {
  
  // Load dependecies.
  const path = require('path');
  const glob = require('glob').sync;
  
  // Initialize configurations.
  const SRC = 'src';
  const DEST = 'public';
  const ENVSIM = require(path.resolve('environment-sim.json'));
  
  // Set environment domain.
  ENVSIM.domain = ENVSIM.environment == 'production' ? ENVSIM.site : `${ENVSIM.environment}.${ENVSIM.site}`;

  // Register paths.
  const PATHS = {
    src: {
      root: SRC,
      engine: {
        root: `${SRC}/engine/`,
        environment: {
          root: `${SRC}/engine/__environment__`,
          php: `${SRC}/engine/__environment__/php`,
          css: `${SRC}/engine/__environment__/css`,
          js: `${SRC}/engine/__environment__/js`,
          assets: `${SRC}/engine/__environment__/assets`,
          images: `${SRC}/engine/__environment__/images`,
          icons: `${SRC}/engine/__environment__/icons`,
          fonts: `${SRC}/engine/__environment__/fonts`,
          config: `${SRC}/engine/__environment__/config`,
          dependencies: {
            php: `${SRC}/engine/__environment__/php/dependencies`,
            js: `${SRC}/engine/__environment__/js/dependencies`,
            css: `${SRC}/engine/__environment__/css/dependencies`
          }
        }
      },
      patterns: {
        root: `${SRC}/patterns`,
        environment: {
          root: `${SRC}/patterns/__environment__`
        }
      },
      data: {
        root: `${SRC}/data`,
        environment: {
          root: `${SRC}/data/__environment__`,
          meta: `${SRC}/data/__environment__/_meta`,
          global: `${SRC}/data/__environment__/_global`,
          shared: `${SRC}/data/__environment__/_shared`,
        },
        site: {
          root: `${SRC}/data/__environment__/__site__`,
          meta: `${SRC}/data/__environment__/__site__/_meta`,
          global: `${SRC}/data/__environment__/__site__/_global`,
          shared: `${SRC}/data/__environment__/__site__/_shared`
        },
        siblings: {
          root: `${SRC}/data/__environment__/__sibling*__`,
          meta: `${SRC}/data/__environment__/__sibling*__/_meta`,
          global: `${SRC}/data/__environment__/__sibling*__/_global`,
          shared: `${SRC}/data/__environment__/__sibling*__/_shared`
        }
      },
      site: {
        root: `${SRC}/__site__`,
        css: `${SRC}/__site__/css`,
        js: `${SRC}/__site__/js`,
        assets: `${SRC}/__site__/assets`,
        images: `${SRC}/__site__/images`,
        icons: `${SRC}/__site__/icons`,
        fonts: `${SRC}/__site__/fonts`,
        dependencies: {
          js: `${SRC}/__site__/js/dependencies`,
          css: `${SRC}/__site__/css/dependencies`
        }
      }
    },
    dest: {
      root: DEST,
      engine: {
        root: `${DEST}/engine/`,
        environment: {
          root: `${DEST}/engine/${ENVSIM.environment}`,
          php: `${DEST}/engine/${ENVSIM.environment}/php`,
          css: `${DEST}/engine/${ENVSIM.environment}/css`,
          js: `${DEST}/engine/${ENVSIM.environment}/js`,
          assets: `${DEST}/engine/${ENVSIM.environment}/assets`,
          images: `${DEST}/engine/${ENVSIM.environment}/images`,
          icons: `${DEST}/engine/${ENVSIM.environment}/icons`,
          fonts: `${DEST}/engine/${ENVSIM.environment}/fonts`,
          config: `${DEST}/engine/${ENVSIM.environment}/config`,
          dependencies: {
            php: `${DEST}/engine/${ENVSIM.environment}/php/dependencies`,
            js: `${DEST}/engine/${ENVSIM.environment}/js/dependencies`,
            css: `${DEST}/engine/${ENVSIM.environment}/css/dependencies`
          }
        }
      },
      patterns: {
        root: `${DEST}/patterns`,
        environment: {
          root: `${DEST}/patterns/${ENVSIM.environment}`
        }
      },
      data: {
        root: `${DEST}/data`,
        environment: {
          root: `${DEST}/data/${ENVSIM.environment}`,
          meta: `${DEST}/data/${ENVSIM.environment}/_meta`,
          global: `${DEST}/data/${ENVSIM.environment}/_global`,
          shared: `${DEST}/data/${ENVSIM.environment}/_shared`,
        },
        site: {
          root: `${DEST}/data/${ENVSIM.environment}/${ENVSIM.site}`,
          meta: `${DEST}/data/${ENVSIM.environment}/${ENVSIM.site}/_meta`,
          global: `${DEST}/data/${ENVSIM.environment}/${ENVSIM.site}/_global`,
          shared: `${DEST}/data/${ENVSIM.environment}/${ENVSIM.site}/_shared`
        },
        siblings: {
          root: `${SRC}/data/${ENVSIM.environment}/__sibling*__`,
          meta: `${SRC}/data/${ENVSIM.environment}/__sibling*__/_meta`,
          global: `${SRC}/data/${ENVSIM.environment}/__sibling*__/_global`,
          shared: `${SRC}/data/${ENVSIM.environment}/__sibling*__/_shared`
        }
      },
      site: {
        root: `${DEST}/${ENVSIM.domain}`,
        css: `${DEST}/${ENVSIM.domain}/css`,
        js: `${DEST}/${ENVSIM.domain}/js`,
        assets: `${DEST}/${ENVSIM.domain}/assets`,
        images: `${DEST}/${ENVSIM.domain}/images`,
        icons: `${DEST}/${ENVSIM.domain}/icons`,
        fonts: `${DEST}/${ENVSIM.domain}/fonts`,
        dependencies: {
          js: `${DEST}/${ENVSIM.domain}/js/dependencies`,
          css: `${DEST}/${ENVSIM.domain}/css/dependencies`
        }
      }
    },
    dependencies: {
      composer: 'vendor',
      npm: 'node_modules'
    }
  };

  // Configure taks.
  grunt.initConfig({
    
    pkg: grunt.file.readJSON('package.json'),
    
    composer: grunt.file.readJSON('composer.json'),
    
    watch: {
      config: {
        files: [
          'Gruntfile.js', 
          'composer.json', 
          'package.json',
          'environment-sim.json'
        ],
        tasks: ['build'],
        options: {
          reload: true
        }
      }, 
      engine: {
        files: [
          `${PATHS.src.engine.environment.root}/**/*`,
          `${PATHS.dependencies.composer}/**/*`
        ],
        tasks: ['phplint', 'copy:engine']
      },
      patterns: {
        files: [
          `${PATHS.src.patterns.root}/**/*`
        ],
        tasks: ['copy:patterns']
      },
      data: {
        files: [
          `${PATHS.src.data.root}/**/*`
        ],
        tasks: ['copy:data']
      },
      site: {
        files: [
          `${PATHS.src.site.root}/.htaccess`,
          `${PATHS.src.site.root}/index.php`,
          `${PATHS.src.site.root}/{css,js,assets,images,icons,fonts}/*`,
        ],
        tasks: ['copy:site']
      }
    },
    
    clean: {
      dest: [PATHS.dest.root]
    },
    
    copy: {
      options: {
        mode: true
      },
      engine: {
        files: [
          {
            expand: true, 
            cwd: PATHS.src.engine.environment.root, 
            src: ['**/*'], 
            dest: PATHS.dest.engine.environment.root,
            dot: true
          },
          {
            expand: true, 
            cwd: PATHS.dependencies.composer, 
            src: ['**/*'], 
            dest: PATHS.dest.engine.environment.dependencies.php
          }
        ]
      },
      patterns: {
        files: [
          {
            expand: true, 
            cwd: PATHS.src.patterns.environment.root, 
            src: ['**/*'], 
            dest: PATHS.dest.patterns.environment.root
          }
        ]
      },
      data: {
        files: [
          {
            expand: true, 
            cwd: PATHS.src.data.site.root, 
            src: ['**/*'], 
            dest: PATHS.dest.data.site.root
          },
          {
            expand: true, 
            cwd: PATHS.src.data.environment.meta,
            src: ['**/*'], 
            dest: PATHS.dest.data.environment.meta
          },
          {
            expand: true, 
            cwd: PATHS.src.data.environment.global,
            src: ['**/*'], 
            dest: PATHS.dest.data.environment.global
          },
          {
            expand: true, 
            cwd: PATHS.src.data.environment.shared,
            src: ['**/*'], 
            dest: PATHS.dest.data.environment.shared
          },
          ...glob(path.resolve(PATHS.src.data.environment.root, '!(__site__)/**/*')).map((sibling) => {
            
            const name = sibling.replace(PATHS.src.data.environment.root, '').split('/')[0];
            const cwd = path.join(PATHS.src.data.environment.root, name);
            const src = sibling.replace(path.resolve(cwd) + '/', '');
            const dest = path.join(PATHS.dest.data.environment.root, name);
            
            return {
              expand: true,
              cwd,
              src,
              dest
            };
            
          })
        ]
      },
      site: {
        files: [
          {
            expand: true,
            cwd: PATHS.src.site.root,
            src: ['**/*'],
            dest: PATHS.dest.site.root,
            dot: true
          }
        ]
      }
    },
    
    phplint: {
      php: [path.join(PATHS.src.engine.environment.php, '**/*.php')]
    },
    
    /*connect: {
      server: {
        options: {
          port: 8000,
          base: PATHS.dest.site.root,
          middleware: [require('connect-livereload')()]
        }
      }
    }*/
    
  });
  
  // Load tasks.
  require('load-grunt-tasks')(grunt);
  
  // Register tasks.
  grunt.registerTask('default', ['dev']);
  grunt.registerTask('build', [
    'unlock',
    'clean',
    'copy'
  ]);
  grunt.registerTask('unlock', 'Unlock public directory for deletion', function () {
    
    // Make asynchronous.
    const done = this.async();
    
    // Change owner of the directory.
    grunt.util.spawn({
      cmd: 'sudo',
      args: ['chown', '-R', '$USER', 'public/'],
      opts: {shell: true, stdio: 'inherit'}
    }, () => done());
    
  });
  grunt.registerTask('dev', [
    'build', 
    //'connect',
    'watch'
  ]);
  grunt.registerTask('dist', [
    'build'
  ]);
  grunt.registerTask('deploy', require(path.resolve('scripts/deploy.js')));
  
};