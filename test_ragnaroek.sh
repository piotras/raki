#!/bin/sh

php -c raki/Ragnaroek/midgard2.ini /usr/bin/phpunit --stop-on-failure -c raki/Ragnaroek/tests/phpunit.xml.dist
