# spacecontrol

Webspace Monitoring

## Requirements

This plugin requires Craft CMS 4.3.5 or later, and PHP 8.0.2 or later.

## Installation

You can install this plugin from the Plugin Store or with Composer. (not yet)

to install:  
copy the spacecontrol folder to your-craft-installation/plugins/
add the following code to your composer.json

```
"repositories": [{
      "type": "path",
      "url": "plugins/spacecontrol"
    }]
```

execute:

```
composer require szenario/craft-spacecontrol
php craft plugin/install spacecontrol
```

#### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “spacecontrol”. Then press “Install”.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require szenario/craft-spacecontrol

# tell Craft to install the plugin
./craft plugin/install spacecontrol
```