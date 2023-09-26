#!/bin/bash

#Declaring used colors
BLUE='\033[1;34m'
RED='\033[0;31m'
GREEN='\033[0;32m'
NC='\033[0m'

#Declaring used functions
getConfigVar () {
	if [ -f "config/configuration.php" ]; then
		grep "\$$1" "config/configuration.php" |awk -F "=" '{print $2}' |awk -F ";" '{print $1}' |tr -d ' ' |tr -d "\'"
	fi
}

getDateString () {
	date '+%Y%m%d_%H%M%S'
}

echo -e "${BLUE}"
echo "    _                 _   _                             "
echo "   | |   ___ __ _ _ _| |_(_)_ __  ___                   "
echo "   | |__/ -_) _\` | ' \  _| | '  \/ -_)                  "
echo "   |____\___\__,_|_||_\__|_|_|_|_\___|      _           "
echo "                     | | | |_ __  __| |__ _| |_ ___ _ _ "
echo "                     | |_| | '_ \/ _\` / _\` |  _/ -_) '_|"
echo "                      \___/| .__/\__,_\__,_|\__\___|_|  "
echo "                           |_|                          "
echo -e "${NC}"

echo -e "\nFor correct operation of this script, please ensure you have zip, unzip, wget and curl installed.\n"

CURRENT_VERSION=$(grep "appVersion" app/Core/AppSettings.php |tr -d [:cntrl:] |tr -d \" |tr -d [:space:] |tr -d ";" |awk -F'=' '{print "v"$2}')
LATEST_RELEASE=$(curl -L -s -H 'Accept: application/json' https://github.com/leantime/leantime/releases/latest)
LATEST_VERSION=$(echo $LATEST_RELEASE | sed -e 's/.*"tag_name":"\([^"]*\)".*/\1/')
DOWNLOAD_URL=$(echo "https://github.com/leantime/leantime/releases/download/$LATEST_VERSION/Leantime-$LATEST_VERSION.zip")

if [ "${CURRENT_VERSION}" == "${LATEST_VERSION}" ]
then
	#No update available
	echo -e "${GREEN}You are already up to date with ${LATEST_VERSION}!${NC}"
else
	#Update available
	echo -e "${RED}There is an update available!${NC}"
	read -r -p "Do you want to update from ${CURRENT_VERSION} to ${LATEST_VERSION}? [Y/n] " chUpd
	case $chUpd in
		Y|y|Yes|yes|"")
			####################
            # BACKUP           #
			####################
			read -r -p "Do you want to create a backup before updating (recommended) [Y/n]" chBck
			case $chBck in
				Y|y|Yes|yes|"")
					echo -e "\n${GREEN}Creating a backup in the 'backup' folder${NC}"
					mkdir -p backup

					printf " - retrieving the database connection details "

					#try to get a connection details from the environment variables if LEAN_DB_USER is detected as an environment variable
					if [ ! -z "$LEAN_DB_USER" ]
					then
						HOST="$LEAN_DB_HOST"
						USER="$LEAN_DB_USER"
						PSWD="$LEAN_DB_PASSWORD"
						DTBS="$LEAN_DB_DATABASE"
						PORT="$LEAN_DB_PORT"
					else
						#try to get connection details from the configuration.php file
						HOST=$(getConfigVar 'dbHost' )
						USER=$(getConfigVar 'dbUser')
						PSWD=$(getConfigVar 'dbPassword')
						DTBS=$(getConfigVar 'dbDatabase')
						PORT=$(getConfigVar 'dbPort')
					fi

					#if user is not found in the environment nor in the configuration.php file
					if [ "$USER" == "" ]
					then
						echo "We were not able to detect the database login credentials. Please enter them manually: "
						read -r -p "\n- Hostname [localhost]:  " HOST
						if [ -z HOST ]
						then
							HOST='localhost'
						fi
						read -r -p "\n- Username: " USER
						read -r -p "\n- Password: " PSWD
						read -r -p "\n- Database [leantime]: " DTBS
						if [ -z DTBS ]
						then
							DTBS='leantime'
						fi
						read -r -p "\n- Portnumber [3306]: " PORT
						if [ -z PORT ]
						then
							PORT='3306'
						fi
					fi

					DT=$(getDateString)
					FILE="leantime_db_backup_$CURRENT_VERSION_$DT.sql.gz"
					printf "(${GREEN}Done${NC})\n"

					#Backing up the database
					printf " - Backing up the database in backup/$FILE "
					mysqldump -h $HOST -P $PORT -u $USER -p$PSWD --no-tablespaces $DTBS | gzip -c > "backup/$FILE"
					#If the file is not found, something went wrong.
					if [ ! -f "backup/$FILE" ] || [ $(stat -c%s "backup/$FILE") -lt 2048 ]
					then
						echo -e "\n${RED}Something went wrong with the database backup. Exiting the update.${NC}\n"
						exit
					else
						echo -e "(${GREEN}Done${NC})\n"
					fi

					#Backing up the files
					FILE="leantime_file_backup_$CURRENT_VERSION_$DT.zip"
					printf " - Backing up the files in backup/$FILE "
					zip -q -r "backup/$FILE" . -x "updates/*" -x "backup/*"

                                        if [ ! -f "backup/$FILE" ] || [ $(stat -c%s "./backup/$FILE") -lt 2048 ]
                                        then
                                                echo -e "\n${RED}Something went wrong with the file backup. Exiting the update.${NC}\n"
                                                exit
                                        else
                                                echo -e "(${GREEN}Done${NC})\n"
                                        fi
					;;

				n|N|No|no)
					echo -e "\n${RED}Skipping the backup${NC}"
					;;
				*)
					echo -e "\n${RED}Invalid response. Exiting the updater.${NC}"
					exit
					;;
			esac

			echo -e "\n${GREEN}Starting the update process${NC}"
			FILE=$(basename "$DOWNLOAD_URL")
			printf " - Downloading the updatefile ($FILE) in the 'update' folder "
			mkdir -p update
			wget -q -O "./update/$FILE" "$DOWNLOAD_URL"
			echo -e "(${GREEN}Done${NC})\n"

			printf " - Extracting the updatefile "
			rm -f -r /tmp/leantime/
			unzip -qq -d /tmp/ "update/$FILE"
                        echo -e "(${GREEN}Done${NC})\n"

			printf " - Applying the update "
			cp -r /tmp/leantime/* .
                        echo -e "(${GREEN}Done${NC})\n"

			printf " - Cleaning up the temporary files "
			rm -r /tmp/leantime/
			echo -e "(${GREEN}Done${NC})\n"

			echo -e "${BLUE}Leantime has been succesfully updated${NC}\n\n"
			;;

		n|N|No|no)
			echo -e "\n${RED}We won't download or apply the update.${NC}";
			exit
			;;

		*)
			echo -e "\n${RED}Invalid response. Exiting the updater.${NC}"
			exit
			;;
	esac



fi

#TODO: capture errors when something didn't work
