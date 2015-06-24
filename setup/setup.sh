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
