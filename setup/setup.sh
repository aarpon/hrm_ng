#!/bin/bash

# Get current folder
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

#
# Propel
#

# Generate SQL
${DIR}/../vendor/bin/propel sql:build  --overwrite \
    --config-dir "${DIR}/../config/db" \
    --schema-dir "${DIR}/../config/db" \
    --output-dir "${DIR}/../config/db"

# Generate classes
${DIR}/../vendor/bin/propel model:build \
    --config-dir "${DIR}/../config/db" \
    --schema-dir "${DIR}/../config/db" \
    --output-dir="${DIR}/../src"

# Runtime connection settings
${DIR}/../vendor/bin/propel config:convert \
    --config-dir "${DIR}/../config/db" \
    --output-dir="${DIR}/../src"

# This will import the schema to the database. It is assumed that
# the database was already created and that the user configured in
# config/propel.yaml has the right to create the tables. Please mind
# that this will drop any existing table! Schema migration has not
# been implemented yet!
${DIR}/../vendor/bin/propel sql:insert \
    --config-dir "${DIR}/../config/db" \
    --sql-dir "${DIR}/../config/db"
