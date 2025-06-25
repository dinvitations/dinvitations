#!/bin/sh
set -e

# Start MinIO server in background
minio server /data --console-address ":8900" &

# Capture the background PID to wait on later
MINIO_PID=$!

# Wait until MinIO HTTP health endpoint is up
until curl -sf http://localhost:9000/minio/health/live >/dev/null; do
  echo "Waiting for MinIO to start..."
  sleep 1
done

# Configure mc and create bucket
mc alias set local http://localhost:9000 "$MINIO_ROOT_USER" "$MINIO_ROOT_PASSWORD"
mc mb --ignore-existing local/templates

# Wait for MinIO to stay running
wait $MINIO_PID
