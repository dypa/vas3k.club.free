docker compose run --rm dev-server bun run update
docker compose run --rm api composer update
docker compose run --rm api composer outdated
docker compose exec api ./bin/console doctrine:query:sql "VACUUM"
