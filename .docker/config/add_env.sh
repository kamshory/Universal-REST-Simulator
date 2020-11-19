#!/bin/bash

set -e

echo "" >> /etc/php/php-custom.ini # new line.

echo "env[CONFIG_DIR] = $CONFIG_DIR;" >>  /etc/php/php-custom.ini
