docker compose run --detach dev-server 
docker compose exec dev-server bun run update
docker compose exec api composer update
docker compose kill dev-server
#docker compose stop -t 1 dev-server
docker compose exec api composer outdated