#!/bin/sh
set -e

: "${MINIO_DEFAULT_BUCKET:=templates}"

# Start MinIO in background with specified data and console ports
minio server /data --console-address ":8900" &

MINIO_PID=$!

# Wait for MinIO server to become available
echo "Waiting for MinIO to become ready..."
until curl -sf http://localhost:9000/minio/health/live >/dev/null; do
  sleep 1
done
echo "MinIO is up."

# Configure mc client alias
mc alias set local http://localhost:9000 "$MINIO_ROOT_USER" "$MINIO_ROOT_PASSWORD"

# Create your default bucket (if not exists)
mc mb --ignore-existing local/"$MINIO_DEFAULT_BUCKET"

# Wait on MinIO to keep container alive
wait "$MINIO_PID"
