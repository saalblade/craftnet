#!/bin/bash

if [[ ! -d ${PROJECT_ROOT}backups/ ]] ; then
    mkdir -p ${PROJECT_ROOT}backups/
fi

PGPASSFILE=${PROJECT_ROOT}.pgpass pg_dump --dbname=${REMOTE_DB_DATABASE} --host=${REMOTE_DB_SERVER} --port=5432 --username=${REMOTE_DB_USER} --if-exists --clean --file=${PROJECT_ROOT}backups/backup.sql --schema=public -w --no-owner --no-privileges --no-acl --exclude-table-data 'public.assetindexdata' --exclude-table-data 'public.assettransformindex' --exclude-table-data 'public.cache' --exclude-table-data 'public.sessions' --exclude-table-data 'public.templatecaches' --exclude-table-data 'public.templatecachecriteria' --exclude-table-data 'public.templatecacheelements'