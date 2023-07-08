docker compose up -d --build

docker exec vas3k-club-reader-api composer install
docker exec vas3k-club-reader-api ./bin/console doctrine:schema:update --complete --force

#because host `web` resolves to external ip, WTF?!
#see nginx conf for description
docker exec vas3k-club-reader-nginx nginx -s reload

x-www-browser "$(docker inspect -f '{{range.NetworkSettings.Networks}}{{.IPAddress}}{{end}}' vas3k-club-reader-nginx):3000/"