docker build -t vas3k.club.reader:v2 .

./run.sh
docker exec vas3k-club-reader composer install
docker exec vas3k-club-reader ./bin/console doctrine:schema:update --force

x-www-browser "$(docker inspect -f '{{range.NetworkSettings.Networks}}{{.IPAddress}}{{end}}' vas3k-club-reader):8000/index.html"
