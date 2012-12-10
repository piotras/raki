#!/bin/bash

# Install dependencies with Composer
wget -q http://getcomposer.org/composer.phar
php composer.phar install --dev

php -c midgard2.ini /usr/bin/phpunit -c tests/phpunit.xml.dist
