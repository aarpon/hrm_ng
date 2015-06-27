#!/bin/bash

#
# Set project directory
#
PROJECT_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )/../

#
# Composer
#

# Make sure all third-party dependencies exist and are up-to-date
if [ ! -d "${PROJECT_DIR}/vendor" ]; then
    # Install
    ${PROJECT_DIR}/composer.phar install
else
    # Update
    ${PROJECT_DIR}/composer.phar update
fi

#
# Propel
#

# Make sure the credentials file exists
if [ ! -f "${PROJECT_DIR}/config/db/propel.yaml" ]; then
    # Copy the sample file
    cp ${PROJECT_DIR}/config/db/propel.yaml.sample ${PROJECT_DIR}/config/db/propel.yaml

    # Inform and leave
    echo "Please add the database credentials to config/db/propel.yaml and restart."
    exit 0
fi

# Make sure the propel.yaml.dist file exists
if [ ! -f "${PROJECT_DIR}/config/db/propel.yaml.dist" ]; then
    # Inform and leave
    echo "Please copy one of config/db/propel.yaml.dist_{mysql|pgsql} to config/db/propel.yaml.dist and restart."
    exit 0
fi

# Generate SQL
${PROJECT_DIR}/vendor/bin/propel sql:build  --overwrite \
    --config-dir "${PROJECT_DIR}/config/db" \
    --schema-dir "${PROJECT_DIR}/config/db" \
    --output-dir "${PROJECT_DIR}/config/db"

# Generate classes
${PROJECT_DIR}/vendor/bin/propel model:build \
    --config-dir "${PROJECT_DIR}/config/db" \
    --schema-dir "${PROJECT_DIR}/config/db" \
    --output-dir="${PROJECT_DIR}/src"

# Runtime connection settings
${PROJECT_DIR}vendor/bin/propel config:convert \
    --config-dir "${PROJECT_DIR}/config/db" \
    --output-dir="${PROJECT_DIR}/src"

# This will import the schema to the database. It is assumed that
# the database was already created and that the user configured in
# config/propel.yaml has the right to create the tables. Please mind
# that this will drop any existing table! Schema migration has not
# been implemented yet!
${PROJECT_DIR}vendor/bin/propel sql:insert \
    --config-dir "${PROJECT_DIR}/config/db" \
    --sql-dir "${PROJECT_DIR}/config/db"
