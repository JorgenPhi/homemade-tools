# Nightly DB Backup - Rclone
This creates sql backups for each table in the specified database list. It also will upload these files to rclone as they are created.

## Usage
1. Run the `backup.sh` script from a terminal.

## Installation 

1. Clone Repo
2. Configure your database variables
3. Install rclone and configure your remote storage
4. Set the rclone remote in the script
5. Run and check that backup was successful
6. Add backup.sh to your crontab