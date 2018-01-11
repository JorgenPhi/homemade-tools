#!/bin/bash

DB_host='localhost'
DB_user='username'
DB_pass='password'
DBS='database1 database2'
DEST="/home/me/sql/"
RCLONECONTAINER="katananightlycrypt"

NOW=`date "+%F-%H%M"`
DIR="$DEST/$NOW"

test -d $DIR || mkdir -p $DIR

echo "Dumping tables into separate SQL files into $DIR"

tbl_count=0
for DB in $DBS # Loop through each database
do
	for t in $(mysql -NBA -h $DB_host -u $DB_user -p$DB_pass -D $DB -e 'show tables') # Loop through every table
	do
		test -d $DIR || mkdir -p $DIR
		echo "DUMPING TABLE: $DB.$t"
		mysqldump -u $DB_user -h $DB_host -p$DB_pass --opt --single-transaction --quick --lock-tables=false $DB $t | gzip > $DIR/$DB-$t.sql.gz
		echo "Uploading $DB.$t"
		# Upload to cloud storage - Large DBs can make huge backups
		rclone move $DEST/ $RCLONECONTAINER: -v
		tbl_count=$(( tbl_count + 1 ))
	done
done

echo "$tbl_count tables dumped into $DIR"
# Again just to make sure we have everything
rclone move $DEST/ $RCLONECONTAINER: -v
rmdir $DIR