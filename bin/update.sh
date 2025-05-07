docker compose exec api ./bin/console doctrine:query:sql "VACUUM"
docker compose run --rm --remove-orphans dev-server bun run update
docker compose run --rm --remove-orphans api composer update --with-all-dependencies
#docker compose run --rm --remove-orphans api composer cs-fix

#https://github.com/composer/composer/issues/12254
#docker compose run --rm --remove-orphans api composer outdated
