  
# Chilicon Tools for Pimcore

Tools contain Symfony commands:

1. `chilicon:db:dump`: Creates database dump of the current Pimcore instance.
1. `chilicon:cache:clear`: Clears Pimcore cache (runs command "`bin/console cache:clear`")  with previous change permissions for "`var`" Pimcore directory.
1. `chilicon:fix-permissions`: Change permissions of the  "`var`" Pimcore directory in purpose of write by server and some system user.

## Copyright and License 
Copyright: (C) 2018 [Chilicon IT](https://www.chilicon-it.de/).

License: [GPL 3.0](gpl-3.0.txt).
