#!/bin/bash

# Usage: build_docs [--public]
#
#    --public: only document public methods and attributes

if [ "$1" = "--public" ] ; then
    levels="public"
    echo "Building public API..."
    dest="./api/public"
else
    levels="public,protected,private"
    echo "Building private API..."
    dest="./api/private"
fi

../vendor/bin/phpdoc -d ../src/hrm/ \
                     -t ${dest} \
                     --ignore "*/Base/*,*/Map/*,/Param/*,/Template/*" \
                     --visibility=${levels} \
                     --template="clean"

# Alternative template: responsive-twig
