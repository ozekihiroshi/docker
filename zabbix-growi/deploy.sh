#!/bin/bash
set -e

# .env の環境変数を読み込む
export $(grep -v '^#' .env | xargs)

# テンプレートを展開
envsubst < docker-compose.template.yml > docker-compose.yml

# コンテナ起動
docker compose up -d
