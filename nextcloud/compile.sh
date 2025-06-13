export DOCKER_BUILDKIT=1
export BUILDKIT_STEP_LOG_MAX_SIZE=10485760
export HTTP_PROXY=http://10.97.12.1:3128
export HTTPS_PROXY=http://10.97.12.1:3128

docker build --progress=plain --no-cache  --build-arg HTTP_PROXY=http://10.97.12.1:3128 --build-arg HTTPS_PROXY=http://10.97.12.1:3128 -t my-custom-php .
docker compose down
docker compose up -d
