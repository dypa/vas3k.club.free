docker compose exec api ./bin/console dbal:run-sql "VACUUM"
docker compose run --rm --remove-orphans dev-server bun run update
docker compose run --rm --remove-orphans api composer update --with-all-dependencies
docker compose run --rm --remove-orphans api composer cs-fix
docker compose exec api ./bin/console cache:clear
docker compose exec api ./bin/console cache:warmup

#https://github.com/composer/composer/issues/12254
#docker compose run --rm --remove-orphans api composer outdated
