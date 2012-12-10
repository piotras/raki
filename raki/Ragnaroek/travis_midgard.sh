#!/bin/bash

if [ -n "$MIDGARD_EXT_VERSION:" ] ; then
	MIDGARD_EXT_VERSION="ratatoskr"
fi

# Install Midgard2 library dependencies
sudo apt-get install -y dbus libglib2.0-dev libgda-4.0-4 libgda-4.0-dev libxml2-dev libdbus-1-dev libdbus-glib-1-dev

# Build Midgard2 core from recent tarball
wget -q https://github.com/midgardproject/midgard-core/tarball/${MIDGARD_EXT_VERSION} -O ${MIDGARD_EXT_VERSION}
tar -xzf ${MIDGARD_EXT_VERSION}
sh -c "cd midgardproject-midgard-core-*&&./autogen.sh --prefix=/usr; make; sudo make install"
rm -f ${MIDGARD_EXT_VERSION}

# Install dependencies with Composer
wget -q http://getcomposer.org/composer.phar
php composer.phar install --dev

php -c midgard2.ini /usr/bin/phpunit -c tests/phpunit.xml.dist
