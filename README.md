  
# Chilicon Tools for Pimcore

Tools contain Symfony commands:

1. `chilicon:db:dump`: Creates database dump of the current Pimcore instance.
1. `chilicon:cache:clear`: Clears Pimcore cache (runs command "`bin/console cache:clear`")  with previous change permissions for "`var`" Pimcore directory.
1. `chilicon:fix-permissions`: Change permissions of the  "`var`" Pimcore directory in purpose of write by server and some system user.

## Installation

### 1. Add to 'composer.json'

    "require": {
        "chilicon-it/pimcore-tools": "dev-develop" 
    },
    "repositories": [
        {
            "type":"package",
            "package":{
                "name":"chilicon-it/pimcore-tools",
                "version":"dev-develop",
                "source":{
                    "type":"git",
                    "url":"git@bitbucket.org:mirasoltek/chilicon-pimcore-tools.git",
                    "reference":"develop" 
                }
            }
        }
    ],
    "autoload": {
        "psr-4": {
            "Chilicon\\Pimcore\\": "vendor/chilicon-it/pimcore-tools/Pimcore" 
        }
    },

### 2. Run Composer

    COMPOSER_MEMORY_LIMIT=4G composer update

### 3. Configure commands

1. Copy file "`vendor/chilicon-it/pimcore-tools/config/chilicon-it.php`" to "`var/config/chilicon-it.php`".
1. Update settings in the "`var/config/chilicon-it.php`" according to requirements of the current project.

Example:

    <?php

    return [
        'hostname' => '', // Leave it empty to use real host name
        'path' => '/chilicon-it/{host}',
        'hostalias' => [
            'some_host_name' => 'some_host_alias',
        ],
    ];

### 4. Set up Symfony service

Open "`app/config/services.yml`" and add following code:

    services:

        # ...

        Chilicon\Pimcore\Command\DbDumpCommand:
            tags:
                - { name: 'console.command', command: 'chilicon:db:dump' }

        Chilicon\Pimcore\Command\ChangePermissionsCommand:
            tags:
                - { name: 'console.command', command: 'chilicon:permissions:change' }

        # ...

## Usage

    bin/console chilicon:db:dump

Etc.

## Copyright and License

Copyright: (C) 2018 [Chilicon IT](https://www.chilicon-it.de/).

License: [GPL 3.0](gpl-3.0.txt).
