#!/bin/bash


MIDGARD_TEST_DB="midgard_raki"
MIDAGRD_TEST_DB_USER="midgard_raki"
MIDGARD_TEST_DB_PASS="midgard_raki"

echo "Dropping test database if exists"
sudo mysqladmin -f drop ${MIDGARD_TEST_DB}
echo "Preparing test database"
sudo mysql -e "CREATE DATABASE ${MIDGARD_TEST_DB} CHARACTER SET utf8";
sudo mysql -e "GRANT all ON ${MIDGARD_TEST_DB}.*  to '${MIDGARD_TEST_DB_USER}'@'localhost' identified by '${MIDGARD_TEST_DB_PASS}'";
sudo mysql -e " FLUSH PRIVILEGES";

echo "Preparing default test content"
php -c ./midgard2.ini ./ragnaroek_content_init.php
