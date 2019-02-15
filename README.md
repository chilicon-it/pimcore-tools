  
# CHILICON IT - Tools for Pimcore

Tools contain Symfony commands:

1. `chilicon:db:dump`: Creates database dump of the current Pimcore instance.
1. `chilicon:cache:clear`: Clears Pimcore cache (runs command "`bin/console cache:clear`")  with previous change permissions for "`var`" Pimcore directory.
1. `chilicon:permissions:change`: Change permissions of the  "`var`" Pimcore directory in purpose of write by server and some system user.

## Installation

### 1. Add to 'composer.json'

    "require": {
        "chilicon-it/pimcore-tools": "dev-master" 
    },
    "repositories": [
        {
            "type":"package",
            "package":{
                "name":"chilicon-it/pimcore-tools",
                "version":"dev-master",
                "source":{
                    "type":"git",
                    "url":"git@github.com:chilicon-it/pimcore-tools.git",
                    "reference":"master" 
                }
            }
        }
    ],
    "autoload": {
        "psr-4": {
            "Chilicon\\Pimcore\\": "vendor/chilicon-it/pimcore-tools/Pimcore" 
        }
    },

For accessing **development version** of the tools please use "`dev-develop`" as *version* and "`develop`" as *reference*.

### 2. Run Composer

    COMPOSER_MEMORY_LIMIT=4G composer update

### 3. Configure commands

1. Copy file "`vendor/chilicon-it/pimcore-tools/config/chilicon-it.php`" to "`var/config/chilicon-it.php`".
1. Update settings in the "`var/config/chilicon-it.php`" according to requirements of the current project.

Example:

    <?php
    
    return [
        'hostname' => '', // Leave it empty to use real host name
        'path' => '/chilicon-it/{host}', # Relative to project root directory
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
        
        Chilicon\Pimcore\Command\ClearCacheCommand:
            tags:
                - { name: 'console.command', command: 'chilicon:cache:clear' }
        
        # ...

## Usage

Please use "`bin/console help <COMMAND>`" for actual information about usage.

### Create database dump

    bin/console chilicon:db:dump

### Change permissions

    bin/console chilicon:permissions:change --user=<USERNAME> --group=<GROUP>
    
    # For example:
    
    # Equals to set of commands:
    # sudo chown -R chilicon:www-data /home/chilicon/work/test/pimcore/workfiles/src/var /home/chilicon/work/test/pimcore/workfiles/src/web/var
    # sudo find /home/chilicon/work/test/pimcore/workfiles/src/var -type f -exec chmod 664 {} \;
    # sudo find /home/chilicon/work/test/pimcore/workfiles/src/var -type d -exec chmod 775 {} \;
    # sudo find /home/chilicon/work/test/pimcore/workfiles/src/web/var -type f -exec chmod 664 {} \;
    # sudo find /home/chilicon/work/test/pimcore/workfiles/src/web/var -type d -exec chmod 775 {} \;
        
    bin/console chilicon:permissions:change --user=chilicon --group=www-data --sudo
    
    # Equals to set of commands:
    # chown -R www-data:www-data /home/chilicon/work/test/pimcore/workfiles/src/var
    # find /home/chilicon/work/test/pimcore/workfiles/src/var -type f -exec chmod 644 {} \;
    # find /home/chilicon/work/test/pimcore/workfiles/src/var -type d -exec chmod 755 {} \;
            
    bin/console chilicon:permissions:change --user=www-data --group=www-data --filemode=644  --dirmode=755 --dir=/var

### Clear cache

    bin/console chilicon:cache:clear --user=<USERNAME> --group=<GROUP>
    
    # For example:
    # chown -R chilicon:www-data /home/chilicon/work/test/pimcore/workfiles/src/var
    # find /home/chilicon/work/test/pimcore/workfiles/src/var -type f -exec chmod 664 {} \;
    # find /home/chilicon/work/test/pimcore/workfiles/src/var -type d -exec chmod 775 {} \;
    # bin/console cache:clear
    # chown -R chilicon:www-data /home/chilicon/work/test/pimcore/workfiles/src/var
    # find /home/chilicon/work/test/pimcore/workfiles/src/var -type f -exec chmod 664 {} \;
    # find /home/chilicon/work/test/pimcore/workfiles/src/var -type d -exec chmod 775 {} \;
        
    bin/console chilicon:cache:clear -u chilicon -g www-data

## Copyright and License

Copyright: (C) 2018-2019 [CHILICON IT](https://www.chilicon-it.de/).

License: [GPL 3.0](gpl-3.0.txt).
