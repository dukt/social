#!/bin/bash

cd ./Source/facebook/

# Create Info.php with plugin version constant

cat > Info.php << EOF
<?php
namespace Craft;

define('FACEBOOK_VERSION', '${PLUGIN_VERSION}');

EOF