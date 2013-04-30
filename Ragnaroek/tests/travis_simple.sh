#!/bin/sh

#set -e
#set -x

# Create live database
MIDGARD_TEST_DB="midgard_live"
MIDGARD_TEST_DB_USER="midgard"
MIDGARD_TEST_DB_PASS="midgard"

echo "Dropping test database if exists"
sudo mysqladmin -f drop ${MIDGARD_TEST_DB}
echo "Preparing test database"
sudo mysql -e "CREATE DATABASE ${MIDGARD_TEST_DB} CHARACTER SET utf8";
sudo mysql -e "GRANT all ON ${MIDGARD_TEST_DB}.*  to '${MIDGARD_TEST_DB_USER}'@'localhost' identified by '${MIDGARD_TEST_DB_PASS}'";
sudo mysql -e " FLUSH PRIVILEGES";

# Import data to live database
gunzip -c tests/openpsa/midgard_openpsademo.sql.gz > tests/openpsa/midgard_openpsademo.sql
mysql -u ${MIDGARD_TEST_DB_USER} -p${MIDGARD_TEST_DB_PASS} -D ${MIDGARD_TEST_DB} < tests/openpsa/midgard_openpsademo.sql

php -c tests/openpsa/midgard2.ini src/Ragnaroek/Ratatoskr/Utils/SchemasCopy.php tests/openpsa/transition-config.php
