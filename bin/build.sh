docker compose build
docker compose build bun dev-server

docker compose up -d --build

docker compose run --rm bun
docker compose exec api composer install
docker compose exec api ./bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

#x-www-browser "$(docker inspect -f '{{range.NetworkSettings.Networks}}{{.IPAddress}}{{end}}' vas3k-club-reader-nginx):3000/"
