# CSV Diff Upload
This tracks the last ID that was backed up and creates a csv file containing all the new entries in the table.
This works for my project because old entries cannot be edited by users. Only new entries to the table are allowed.

## Usage
1. Run the `upload-csv.php` script from a terminal.

## Installation 

1. Clone Repo
2. Configure your database variables
3. Modify to support your specific application
4. Install the internet archive library
```
sudo apt install python-pip gzip
sudo pip install internetarchive
ia configure
	// Follow on screen instructions
```

5. Create the database for keeping track of the last uploaded ID. Give the backupbot user permission to create SQL outfiles & read-only access to application database.
`mysql -u root -p`
```sql
CREATE DATABASE `backupstats`;
CREATE TABLE `backupstats` (
  `board` varchar(255) NOT NULL,
  `lastid` int(11) NOT NULL,
  `timestamp` int(11) NOT NULL
) ENGINE=TokuDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `backupstats`
  ADD UNIQUE KEY `board` (`board`);

CREATE USER 'backupbot'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON `backupbot`. * TO 'backupbot'@'localhost';
GRANT FILE ON *.* TO 'backupbot'@'localhost';
GRANT SELECT ON `foolfuuka`.`ff_boards` TO 'backupbot'@'localhost';
GRANT SELECT ON `asagi`.* TO 'backupbot'@'localhost';
FLUSH PRIVILEGES;
exit
```