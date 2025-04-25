#!/bin/sh
echo "Waiting for front to be ready..."
until curl -s http://front:3000 > /dev/null; do
    echo "Front is not ready yet, retrying in 2 seconds..."
    sleep 2
done
echo "Front is ready, starting NGINX..."
exec nginx -g "daemon off;"
