docker compose run --detach dev-server 
docker compose exec dev-server bun run update
docker compose exec api composer update
docker compose stop dev-server
docker compose exec api composer outdated