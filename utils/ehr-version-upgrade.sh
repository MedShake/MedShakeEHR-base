#!/bin/bash
# Provides the functions set to upgrade existing EHR containers to latest data & file structure
# according to the core EHR scripts and changes.
# It will used the /upgrade/ SQL & PHP scripts from the various EHR modules in sequence.
# The whole process is governed from the DB version values in the system database.
# SQL scripts should be named like sqlUpgrade_vU.V.W_vX.Y.Z.sql with U,V,W,X,Y & Z beeing a number.
# PHP scripts should be named like sqlUpgrade_vU.V.W_vX.Y.Z_poste.php with U,V,W,X,Y & Z beeing a number. 
# PHP scripts will be called with a URL like https://$EHR_SERVER_NAME/configuration/exec/base/$entity/
# entity beeing the name of the PHP script file to apply stripped from the .php suffix.
#
# Usual steps are (FYI, $EHR_BASE is the root folder of EHR base and $EHR_APP is the location of your app's data) :
#
# 1- SETUP
#  Setup the containers env to map the existing environement to upgrade
#  # cd $EHR_APP; . ./env.sh
#
# 2- SERVICE UP
#  Ensure service is up
#  # ./ehr-up.sh
#
# 3- LINK MODULES UPGRADES
#  If upgrade files of modules are not in subfolders of the upgrade/ folder of the app, link the upgrades of the module by calling for each module : 
#  > $EHR_BASE/utils/ehr-version-upgrade.sh linkModuleUpgrade modulename pathtorootofthemodule
#
# 4- LOGIN
#  Login as an admin user to be able to perform PHP upgrades
#  > $EHR_BASE/utils/ehr-version-upgrade.sh login an-admin-user the-password-of-this-admin
#
# 5- ONE STEP UPGRADE
#  Perform one step of upgrade ad lib
#  > $EHR_BASE/utils/ehr-version-upgrade.sh upgradeOneStep base
#
#  On error during PHP upgrade, get the version from which the process was trying to upgrade (like v1.2.3) from the output and launch again with that version as parameter like :
#  > $EHR_BASE/utils/ehr-version-upgrade.sh upgradePHPOneStep base v1.2.3
#
#  Launch as much as one step upgrade required until you get the message :
#  "WARN No upgrade file found"
# 6- LOGOUT
#  Logout to ensure cookies are deleted
#  > $EHR_BASE/utils/ehr-version-upgrade.sh logout
#
# 7- UNLINK MODULES UPGRADES
#  Unlink any module upgrades by calling for each of them:
# > $EHR_BASE/utils/ehr-version-upgrade.sh unlinkModuleUpgrade modulename
#
# Test & Enjoy :)

# Fetch the version of a given EHR module
# @param module The name of the EHR module to find (eg "base", "gynobs" ...)
# @return the key of the version as a string (eg. "v1.1.0")
fetchVersionModule(){
 local module=$1
 local dbInstance=$(fetchDBDockerKey)
 docker exec -it $dbInstance mysql -s -N -Dmedshakeehr -uroot -p"$EHR_DBMS_ROOT_PASSWORD" -e "select value from system where name = \"$module\"" | tr -d '\r'
}

# Fetch the id of the DB container
# @return the Docker's ID
fetchDBDockerKey(){
 docker-compose ps -q "db"
}

# Fetch the id of the Web container
# @return the Docker's ID
fetchWebDockerKey(){
 docker-compose ps -q "web"
}

# Fetch the location of this script
# @return the path of this script
fetchScriptPath(){
 dirname `which $0`
}

# Link the upgrade of a module to the base upgrade folder
# @param module the module to link
# @paral basePath the location of the module to link
linkModuleUpgrade(){
 local module=$1
 local modulePath=$2
 local basePath=$(fetchScriptPath)
 ln -s $modulePath/upgrade/$module/ $basePath/../upgrade/$module
}

# Unlink the upgrade of a module from the base upgrade folder
# @param module the module to unlink
unlinkModuleUpgrade(){
 local module=$1
 local basePath=$(fetchScriptPath)
 unlink $basePath/../upgrade/$module
}


# Build the Upgrade Entity from the script name
# @param fileName the name of the script file
# @return the matching entity
buildPHPUpgradeEntity(){
 local fileName=$1
 local regexEntity="(sqlUpgrade_v[0-9\.]+_v[0-9\.]+_post)\.php"
 [[ $fileName =~ $regexEntity ]]
 echo ${BASH_REMATCH[1]}
}

# Build the target version from a script file name
# @param fileName the name of the file to use
# @return the target version
buildTargetVersion(){
 local fileName=$1
 local regexEntity="sqlUpgrade_v[0-9\.]+_(v[0-9\.]+)(\.sql|_post\.php)"
 [[ $fileName =~ $regexEntity ]]
 echo ${BASH_REMATCH[1]}
}

# Find the SQL's matching upgrade script 
# @param module the module id
# @param fromVersion the version to match
# @return the SQL Script file matching the fromVersion
findSQLScriptUpgrade(){
 local module=$1
 local fromVersion=$2
 local scriptPath=$(fetchScriptPath)
 find $scriptPath/../upgrade/$module/ -type f -name "sqlUpgrade_$fromVersion*.sql"
}

# Find the PHP's matching upgrade script
# @param module the module id
# @param fromVersion the version to match
# @return the PHP Script file matching the fromVersion
findPHPScriptUpgrade(){
 local module=$1
 local fromVersion=$2
 local scriptPath=$(fetchScriptPath)
 find $scriptPath/../upgrade/$module/ -type f -name "sqlUpgrade_$fromVersion*_post.php"
}

# Execute a SQL update script
# @param scriptFile the file of the script to use
execSQLScript(){
 local scriptFile=$1
 local dbInstance=$(fetchDBDockerKey)
 echo "Trying to upgrade DB with" $scriptFile
 docker exec -i $dbInstance mysql -Dmedshakeehr -uroot -p"$EHR_DBMS_ROOT_PASSWORD" < $scriptFile
}

# Login in an EHR session
# @param userName name of an admin
# @param userPassword password of this admin
login(){
 local userName=$1
 local userPassword=$2
 echo "$(tput setaf 10)FINE$(tput sgr0) Login with user \'$userName\' \'$userPassword\'"
 curl -Liv -c /tmp/ehr-cookie.txt -H "Accept-Language: en,fr" -d "formIN=baseLogin" -d "p_username=$userName" -d "p_password=$userPassword" http://$EHR_SERVER_NAME/login/logInDo/
 echo "$(tput setaf 10)FINE$(tput sgr0) User logged in and session cookies stored for later auto-login"
}

# Logout from the session by removing the local cookies stored
logout(){
 rm -f /tmp/ehr-cookie.txt
}

# Execute a PHP call to perform the PHP update required
# @param entity the name of the HTTP entity name part used as part of the API
execPHPEntity(){
 local module=$1
 local entity=$2
 #local userName=$3
 #local userPassword=$4
 #local webInstance=$(fetchWebDockerKey)
 #echo "$(tput setaf 10)FINE$(tput sgr0) About to login with user" $userName
 #curl -Liv -c /tmp/ehr-cookie.txt -d "formIN=baseLogin" -d "p_username=$userName" -d "p_password=$userPassword" http://med1.medgence.com/login/logInDo/
 echo "$(tput setaf 10)FINE$(tput sgr0) About to call" $entity
 curl -Liv -b /tmp/ehr-cookie.txt -H "Accept-Language: en,fr" http://$EHR_SERVER_NAME/configuration/exec/$module/$entity/
}

# Trigger a PHP script upgrade. You will be asked an EHR admin user and its password for HTTP access granting.
# @param module the module id
# @param userName the admin's user name to use for HTTP access
# @param userPassword the admin's user password for HTTP access
upgradePHPOneStep(){
 local module=$1
 local fromVersion="$2"
 if [ -z "$fromVersion" ]; then
  # If no version was set in call then fetch latest version of module as per the DBMS config
  fromVersion=$(fetchVersionModule $module)
  echo "$(tput setaf 10)FINE$(tput sgr0) Defaulting to current version for module" $module
 fi
 echo "$(tput setaf 10)FINE$(tput sgr0) Current version for" $module "module is" $fromVersion
 local scriptFile=$(findPHPScriptUpgrade $module $fromVersion)
 if [ -z "$scriptFile" ]; then
  # exit with code -1
  echo "$(tput setaf 10)WARN$(tput sgr0) No PHP upgrade file found for version" $fromVersion
  return -1
 fi
 local entity=$(buildPHPUpgradeEntity $scriptFile)
 echo "$(tput setaf 10)FINE$(tput sgr0) Found the update matching entity" $entity
 local toVersion=$(buildTargetVersion $scriptFile)
 echo "$(tput setaf 10)FINE$(tput sgr0) Attempt to PHP upgrade" $module "module from current version" $fromVersion "to version" $toVersion
 execPHPEntity $module $entity
 echo "$(tput setaf 10)DONE$(tput sgr0) PHP Upgrade performed"
}

# Initialize the environment variables
initEnv(){
 SCRIPT_PATH=$(fetchScriptPath)
 #. $SCRIPT_PATH/env.sh
 echo "Script path found : $SCRIPT_PATH"
dbInstance= $(fetchDBDockerKey)
}

# Perform a one step upgrade of the current EHR setup
# @param module the id of the module (like base, gynobs, etc)
upgradeOneStep(){
 local module=$1
 local fromVersion=$(fetchVersionModule $module)
 echo "$(tput setaf 10)FINE$(tput sgr0) Called upgrade for $module module from current version $fromVersion"
 # First let's perform  a corresponding SQL upgrade
 local upgradeSQLScript=$(findSQLScriptUpgrade $module $fromVersion)
 #echo $upgradeSQLScript
 if [ -z "$upgradeSQLScript" ]; then
  # exit with code -1
  echo "$(tput setaf 10)WARN$(tput sgr0) No upgrade file found for version" $fromVersion
  return -1
 fi
 local toVersion=$(buildTargetVersion $upgradeSQLScript)
 echo "$(tput setaf 10)FINE$(tput sgr0) Attempt to SQL upgrade" $module "module from current version" $fromVersion "to version" $toVersion "with SQL script" $upgradeSQLScript
 execSQLScript $upgradeSQLScript
 upgradePHPOneStep $module $fromVersion
 echo "$(tput setaf 10)DONE$(tput sgr0) Upgraded version" $fromVersion "to version" $toVersion
}

# Default function
init(){
 initEnv 
}

calledProcedure=${1:-init}
shift
echo "$(tput setaf 10)FINE$(tput sgr0) Calling procedure $calledProcedure with parameters : $@"

# Those two lines are required to be  written in any script using this library
#
eval $calledProcedure $@
echo "$(tput setaf 10)DONE$(tput sgr0) Called $calledProcedure"

