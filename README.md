# Templating Engine

> A PHP templating engine for powering the Emory Libraries website


## Prerequisites

This templating engine requires PHP 7 or greater. For development, this project requires [Node.js](https://nodejs.org) for its development environment, [npm](https://npmjs.com) for package and dependency management, [composer](https://getcomposer.org) for dependency management, [Grunt](https://gruntjs.com) for task automation, and [git](https://git-scm.com) for version control.


## Installation

Prior to installation, ensure that all prerequisites have been met. Then, to install this project on your system:

1. Download and unzip the compressed package, or clone the repo using:

```
git clone https://github.com/emory-libraries/templating-engine
```

2. Then install all dependencies in one fell swoop:

```
npm install && composer install
```

## Contributing

Before contributing to the Templating Engine, make sure all [prerequisites](#prerequisites) have been met and that you followed the [instillation steps](#installation) to setup the project on your system. Below is some additional information that you'll also need to know before getting started.

### Working with Data

The project's `src/data` folder is empty by default when cloning the repo. To get starting with development and/or testing, you'll either want to (a) create some sample data files to work or (b) pull a copy of the existing data files that have been generated via the CMS from the Emory Libraries' server (preferred). Data files can use any of the following formats: `json`, `xml`, `yaml`, or `yml`.

### Simulating a Server Environment

The project includes an `environment-sim.json` file that is used to simulate the Emory Libraries' live server architecture during development. You can configure this based on the `site` and `environment` that you wish to simulate. These configurations are then used during the build process ([`grunt build`](#grunt-build)) to generate the `public/` folder.

### Automating the Workflow

Grunt has been preconfigured with a number of usefual tasks that will help automate your workflow. The most commonly used Grunt task is [`grunt dev`](#grunt-dev), which is the default. A complete list of Grunt tasks that are available to you include:

#### `grunt dev`

Builds the templating engine, outputting it in `public/` folder, then listens for file changes and automatically rebuilds when detected.

#### `grunt build`

Performs a one-time build of the templating engine and outputs it in the `public/` folder.

#### `grunt dist`

An alias for [`grunt build`](#grunt-build).

#### `grunt unlock`

Runs a `sudo` operation that requires the current user's password in order to modify the permissions of templating engine's `cache/` folder. 

> The templating engine's `cache/` folder and its contents are owned by the PHP engine by default. This is required in order to be able to wipe the `public/` folder before rebuilding it during the [`grunt build`](#grunt-build) process.

#### `grunt deploy`

Prompts the user to choose a deployment destination and supply their WebDAV username and password, then deploys the templating engine files to the chosen destination.

> In order to run a deployment, you must have proper WebDAV permissions to access and modify the Emory Libraries' web server. The username and password that you use to deploy files to the server will be specific to your WebDAV user account.