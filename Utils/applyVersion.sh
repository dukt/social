#!/bin/bash

cd ./Source/social/

# Create Info.php with plugin version constant

cat > Info.php << EOF
<?php
namespace Craft;

define('SOCIAL_VERSION', '${PLUGIN_VERSION}');

EOF