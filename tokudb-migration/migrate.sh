#!/bin/bash

DB_host='localhost'
DB_user='username'
DB_pass='password'
DBS='database1 database2'

echo "Generating alter_tables.sql"
echo "" > alter_tables.sql

for DB in $DBS do
        TABLES=`echo "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$DB' AND engine = 'InnoDB'" | mysql -h $DB_host -u $DB_user -p$DB_pass $DB | grep -v Tables_in | grep -v TABLE_NAME`
        for TABLE in ${TABLES[@]}; do
                echo "ALTER TABLE $DB.$TABLE ENGINE=TokuDB;" >> alter_tables.sql
        done
done
echo "Generated!"
