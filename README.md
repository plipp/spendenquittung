Spendenquittung
===============
Wordpress plugin, which is used by the [*buechertisch.org*](http://buechertisch.org/buecher-spenden/spendenquittung/) to
issue their donators with a donation receipt.

The plugin documentation, following the wordpress guidelines, can be found in the [wordpress plugin readme](readme.txt)

WP-Development-Options
----------------------
- [Vagrantpress](https://github.com/chad-thompson/vagrantpress)
- Docker: s. [docker-compose.yml](./docker-compose.yml)

### Local PHP-Installations (e.g. for your IDE)

```bash
  
    $  dnf install php
    $  dnf install php-composer-installers
```

Notes:

- `/usr/bin/composer == composer.phar`
- with Docker or Vagrant the complete LAMP-Stack is not required!

### Testing

There exist various unit tests, which can be run via
 
```bash
   
    $ cd $wordpress/wp-content/plugins/spendenquittung
    $ composer update
    $ vendor/bin/phpunit -c phpUnit.xml
```

Run `./tests/test-completePdf.php` to test PDF generation.


Known Issues
------------
- APIs
  -ZVAB must check both ISBN10 + 13 (for ZVAB, both is different)
- js/css not yet minified (-> introduce: grunt or gulp)
- plugin installation/deactivation/deinstallation concept which reflects DB-changes.
