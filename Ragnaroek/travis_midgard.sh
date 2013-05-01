#!/bin/bash

if [ -n "$MIDGARD_EXT_VERSION:" ] ; then
	MIDGARD_EXT_VERSION="ratatoskr"
fi

# Install Midgard2 library dependencies
sudo apt-get install -y dbus libglib2.0-dev libgda-4.0-4 libgda-4.0-dev libxml2-dev libdbus-1-dev libdbus-glib-1-dev libgda-4.0-mysql

# Build Midgard2 core from recent tarball
wget -q https://github.com/midgardproject/midgard-core/tarball/${MIDGARD_EXT_VERSION} -O ${MIDGARD_EXT_VERSION}
tar -xzf ${MIDGARD_EXT_VERSION}
sh -c "cd midgardproject-midgard-core-*&&./autogen.sh --prefix=/usr; make; sudo make install"
rm -f ${MIDGARD_EXT_VERSION}

# Build and install Midgard2 PHP extension
wget -q https://github.com/midgardproject/midgard-php5/tarball/${MIDGARD_EXT_VERSION} -O ${MIDGARD_EXT_VERSION}
tar zxf ${MIDGARD_EXT_VERSION}
sh -c "cd midgardproject-midgard-php5-*  && phpize && ./configure && sudo make install"
echo "extension=midgard2.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`
