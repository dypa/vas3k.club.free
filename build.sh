docker build -t vas3k.club.free:v1 .

./run.sh
docker exec vas3k-club-free composer install
docker exec vas3k-club-free ./bin/console doctrine:schema:update --force

x-www-browser "$(docker inspect -f '{{range.NetworkSettings.Networks}}{{.IPAddress}}{{end}}' vas3k-club-free):8000/index.html"
