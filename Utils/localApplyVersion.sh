#!/bin/bash

export PLUGIN_NAME="analytics"
export PLUGIN_NAME_UP="ANALYTICS"

for VERSION in "$@"

do

./Utils/applyVersion.sh ${VERSION}

done
