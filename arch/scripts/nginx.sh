#!/usr/bin/env bash
set -e
set -o pipefail

#Update nginx config and start it
echo ">>Copying nginx configuration..."
# replace variables in template and copy to nginx config directory
envsubst '\$APP_WORKSPACE' < "$WORKING_DIR/$APP_WORKSPACE/arch/nginx/http.d/$APP_ENV.conf.template" > /etc/nginx/http.d/default.conf
echo ">>Starting nginx..."
nginx || exit 1
