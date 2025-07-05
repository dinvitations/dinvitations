#!/bin/sh
set -e

: "${MINIO_DEFAULT_BUCKET:=templates}"
: "${MINIO_UPLOAD_BUCKET:=uploads}"

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

# Create buckets if they don't exist
mc mb --ignore-existing local/"$MINIO_DEFAULT_BUCKET"
mc mb --ignore-existing local/"$MINIO_UPLOAD_BUCKET"

# Set bucket policies
mc anonymous set none local/"$MINIO_DEFAULT_BUCKET"    # private
mc anonymous set download local/"$MINIO_UPLOAD_BUCKET" # public (read-only)

echo "Buckets created:"
echo "- $MINIO_DEFAULT_BUCKET (private)"
echo "- $MINIO_UPLOAD_BUCKET (public)"

# Wait on MinIO to keep container alive
wait "$MINIO_PID"
