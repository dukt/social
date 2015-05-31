#!/bin/bash

export INFO_PATH="Source/social/Info.php"
export CONSTANT_NAME="SOCIAL"

for VERSION in "$@"

do

# Create Info.php with plugin version constant

cat > $INFO_PATH << EOF
<?php

namespace Craft;

define('${CONSTANT_NAME}_VERSION', '$VERSION');

EOF

done
