docker compose exec api ./bin/console doctrine:query:sql "VACUUM"
docker compose run --rm --remove-orphans dev-server bun run update
docker compose run --rm --remove-orphans api composer update
#docker compose run --rm --remove-orphans api composer cs-fix
docker compose run --rm --remove-orphans api composer outdated

