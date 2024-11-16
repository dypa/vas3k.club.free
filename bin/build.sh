docker compose build
docker compose build bun dev-server

docker compose up -d --build

docker compose run --rm bun
docker compose exec api composer install
docker compose exec api ./bin/console doctrine:query:sql "PRAGMA journal_mode = WAL"
docker compose exec api ./bin/console doctrine:query:sql "PRAGMA synchronous = NORMAL"
docker compose exec api ./bin/console doctrine:query:sql "PRAGMA locking_mode = NORMAL"
docker compose exec api ./bin/console doctrine:schema:update --force

#x-www-browser "$(docker inspect -f '{{range.NetworkSettings.Networks}}{{.IPAddress}}{{end}}' vas3k-club-reader-nginx):3000/"
