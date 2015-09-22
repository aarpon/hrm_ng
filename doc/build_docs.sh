#!/bin/bash

# Please note: apugen requires sqlite; install with
#
# sudo apt-get install php5-sqlite
#
# Usage: build_docs [--public]
#
#    --public: only document public methods and attributes

if [ $1 = "--public" ] ; then
    levels="public"
    echo "Building public API..."
    dest="./api/public"
else
    levels="public,protected,private"
    echo "Building private API..."
    dest="./api/private"
fi

../vendor/bin/apigen generate \
                        --source ../src/hrm/ \
                        --destination ${dest} \
                        --exclude=Base/*,Map/*,Param/*,Template/*,User/Base/*,User/Map/* \
                        --tree \
                        --no-source-code \
                        --access-levels=${levels} \
                        --template-theme=bootstrap
