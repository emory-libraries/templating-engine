module.exports = async function () {
  
  // Load dependencies.
  const inquirer = require('inquirer');
  const chalk = require('chalk');
  const path = require('path');
  const glob = require('glob').sync;
  const fs = require('fs-extra');
  const {createClient} = require('webdav');
  const _ = require('lodash');
  
  // Make the task asynchronous.
  const done = this.async();
  
  // Get the simulated environment settings
  const envsim = require(path.resolve('environment-sim.json'));
  
  // Set environments and their remote folder names.
  const env = {
    'development':  'dev',
    'qa':           'qa',
    'staging':      'staging',
    'production':   'prod'
  };
  
  // Identify the local and remote paths to deploy.
  const paths = {
    'src/engine/__environment__/.env': 'engine/{{environment}}/.env',
    'src/engine/__environment__/meta': 'engine/{{environment}}/meta',
    'src/engine/__environment__/config': 'engine/{{environment}}/config',
    'src/engine/__environment__/css': 'engine/{{environment}}/css',
    'src/engine/__environment__/js': 'engine/{{environment}}/js',
    'src/engine/__environment__/icons': 'engine/{{environment}}/icons',
    'src/engine/__environment__/logos': 'engine/{{environment}}/logos',
    'src/engine/__environment__/images': 'engine/{{environment}}/images',
    'src/engine/__environment__/php': 'engine/{{environment}}/php',
    'src/patterns/__environment__': 'patterns/{{environment}}',
    'vendor': 'engine/{{environment}}/php/dependencies',
  };
  
  // Initialize prompts.
  const answers = await inquirer.prompt([
    {
      name: 'environment',
      type: 'list',
      message: 'What is the environment you wish to deploy to?',
      choices: Object.keys(env),
      default: envsim.environment
    },
    {
      name: 'username',
      type: 'input',
      message: 'Enter your WebDAV username.',
      validate(username) {
        
        // Verify that a username has been given.
        if( username !== null && username !== undefined && username !== '' ) return true;
        
        // Otherwise, indicate the username is required.
        return 'Username is required.';
        
      }
    },
    {
      name: 'password',
      type: 'password',
      message: 'Enter your WebDAV password.',
      mask: '*',
      validate(password) {
        
        // Verify that a password has been given.
        if( password !== null && password !== undefined && password !== '' ) return true;
        
        // Otherwise, indicate the password is required.
        return 'Password is required.';
        
      }
    },
    {
      name: 'continue',
      type: 'confirm',
      message: (answers) => chalk`You are about to deploy files to {cyan ${answers.environment}}.\n  {bold Do you wish to continue?}`,
      default: false
    }
  ]);
    
  // Verify that the user wishes to continue.
  if( answers.continue ) {

    // Get the local files to deploy.
    const files = _.reduce(paths, (files, remote, local) => {

      // Get the remote and local paths
      remote = remote.replace('{{environment}}', env[answers.environment]);
      local = path.resolve(local);
      
      // Find the local file.
      if( fs.statSync(local).isFile() ) files.push({
        src: local,
        dest: remote
      });
      
      // Otherwise, find all files at the local path.
      else files = files.concat(glob(path.join(local, '**')).map((file) => {

        // Get the file's paths and contents.
        return {
          src: file,
          dest: path.join(remote, file.replace(local, ''))
        };

      }));

      // Continue reducing.
      return files;

    }, []);

    // Create a webdav client.
    const client = createClient('https://files.web.emory.edu/site/LibraryWeb/', {
      username: answers.username,
      password: answers.password
    });
    
    // Attempt to write all local files and folders to the remote server.
    try {

      // Write the files and folders to the remote server.
      for( let file of files ) {

        // Get the file or folder source and destination.
        const src = file.src;
        const dest = file.dest;

        // Ensure that folders exist on the remote server.
        if( fs.statSync(src).isDirectory() ) {

          // Determine if the directory already exists.
          try {

            // Attempt to get some information about the directory, and if it doesn't throw an error, assume the directory exists.
            await client.stat(dest);

          } 

          // Otherwise, create the directory if it doesn't exist.
          catch(error) {

            // Only create the directory if it doesn't already exist.
            if(error.response && error.response.status == 404 ) await client.createDirectory(dest);

          }

        }

        // Otherwise, ensure that files get copied to the remote server.
        else {

          // Get the file's contents.
          const contents = fs.readFileSync(src, 'utf8');

          // For meta, global, and shared content, force the user to confirm before overwriting.
          if( src.indexOf('/_meta/') > -1 || src.indexOf('/_global/') > -1 || src.indexOf('/_shared/') > -1 ) {

            // Check to see if the file exists on the remote server.
            try {

              // Attempt to get some information about the file, and if it doesn't throw an error, assume the file exists.
              await client.stat(dest);

              // If the file exists, require the user confirm that they wish to overwrite the file.
              const confirm = await inquirer.prompt([
                {
                  name: 'overwrite',
                  type: 'confirm',
                  message: `The remote file ${dest} already exists. Do you wish to overwrite it?`,
                  default: false
                }
              ]);

              // Only overwrite the file if the user confirmed.
              if( confirm.overwrite ) await client.putFileContents(dest, contents);

            } 

            // Otherwise, create the file if it doesn't exist.
            catch(error) {

              // Create the file because it does not already exist.
              await client.putFileContents(dest, contents);

            }

          }

          // For everything else, overwrite without confirming.
          else await client.putFileContents(dest, contents);

        }

      }

      // Report success.
      console.log(chalk`\nFiles were successfully deployed to {green.bold ${answers.environment}}.\n`);

      // Done.
      done();
      
    }
    
    // Otherwise, alert the user when errors occur.
    catch(error) {
      
      // Report errors.
      console.error(chalk`\nAn error occurred while trying to deploy files to {red.bold ${answers.environment}}:\n\n${error}\n`);
      
      // Done.
      done();
      
    }

  }

  // Otherwise, done.
  else done();
  
};