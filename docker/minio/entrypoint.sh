#!/bin/sh

# Start MinIO server in the background
minio server /data --console-address ":8900" &

# Wait until MinIO HTTP health endpoint is up
until curl -s http://localhost:9000/minio/health/live; do
  echo "Waiting for MinIO to start..."
  sleep 1
done

mc alias set local http://localhost:9000 $MINIO_ROOT_USER $MINIO_ROOT_PASSWORD

mc mb --ignore-existing local/templates
mc anonymous set-json /policy/template-previews-policy.json local/templates

wait $!
