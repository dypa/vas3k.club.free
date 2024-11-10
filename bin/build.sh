docker compose build
docker compose build bun dev-server

docker compose up -d --build

docker compose run --rm bun install
docker exec vas3k-club-reader-api composer install
docker exec vas3k-club-reader-api ./bin/console doctrine:query:sql "PRAGMA journal_mode = WAL"
docker exec vas3k-club-reader-api ./bin/console doctrine:query:sql "PRAGMA synchronous = NORMAL"
docker exec vas3k-club-reader-api ./bin/console doctrine:query:sql "PRAGMA locking_mode = NORMAL"
docker exec vas3k-club-reader-api ./bin/console doctrine:schema:update --force

#x-www-browser "$(docker inspect -f '{{range.NetworkSettings.Networks}}{{.IPAddress}}{{end}}' vas3k-club-reader-nginx):3000/"
