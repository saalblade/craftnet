#!/bin/bash

PGPASSFILE=${PROJECT_ROOT}.pgpass psql --dbname=${LOCAL_DB_DATABASE} --host=${LOCAL_DB_SERVER} --port=5432 --username=${LOCAL_DB_USER} < ${PROJECT_ROOT}backups/backup.sql

curl -u ${HTTP_AUTH_USER}:${HTTP_AUTH_PASS} -X POST https://staging.id.craftcms.com/index.php/actions/app/migrate
