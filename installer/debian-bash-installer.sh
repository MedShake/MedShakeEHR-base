#! /bin/bash

# This file is part of MedShakeEHR.
#
# Copyright (c) 2020
# Michaël Val 
# MedShakeEHR is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# any later version.
#
# MedShakeEHR is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MedShakeEHR.  If not, see <http://www.gnu.org/licenses/>.

# Installateur de base
#
# @author Michaël Val

selectMsehrPath() {
    read -e -i "$msehrPath" -p "Choix du dossier d'installation [défaut : /opt/ehr] : " input
    msehrPath="${input:-$msehrPath}"
}

selectPackages() {
    echo "Installation des dépendances de MedShakeEHR minimales, tapez 1 [défaut]"
    echo "Installation de MedShakeEHR avec Orthanc (Phonecapture, Echographe ...), tapez 2"
    echo "Ne rien installer, tapez 3"
    read -e -i "$selectInstall" -p "Choix : " input
    selectInstall="${input:-$selectInstall}"
    case $selectInstall in
        "1" )
            packagesInstall ;;
        "2" )
            msehrDep="${msehrDep} ${extraDicom}"
            packagesInstall
            orthancConfig ;;
        "3" ) 
		 ;;
        * ) 
            echo "Mauvaise valeur saisie"
            selectPackages ;;
    esac 
}

packagesInstall(){
	apt update && apt upgrade -y && apt install -y $msehrDep
}

orthancConfig(){
    read -p "Choix du nom de l'utilisateur d'Orthanc : " orthancUser
    echo
    while true; do
        read -s -r -p "Choix du mot de passe utilisateur d'Orthanc (ne pas utiliser les caractères : \"'$,[]*?{}~#%\<,>|^; ) : " orthancPswd
        echo
        read -s -r -p "Confirmation du mot de passe : " orthancPswd1
        echo
        [ "$orthancPswd" = "$orthancPswd1" ] && break || echo "Essayez encore"
    done
    sed -i 's/"AuthenticationEnabled" : false,/"AuthenticationEnabled" : true,/' /etc/orthanc/orthanc.json
    sed -i "s|// \"alice\" : \"alicePassword\"|\"$orthancUser\" : \"$orthancPswd\"|" /etc/orthanc/orthanc.json
}

selectLampConfig() {
read -e -i "$selectLampConfig" -p "Vous souhaitez que le serveur LAMP soit configuré par défaut, tapez 1 [défaut], vous voulez configurer le serveur LAMP tapez 2, si vous l'avez déjà configuré tapez 3 : " input
selectLampConfig="${input:-$selectLampConfig}"
case $selectLampConfig in
    "1" )
        certGen
        apacheConfig
        mariadbConfig ;;       
    "2" )
        selectdomain
        certGen
        apacheConfig
        mariadbConfig ;;
    "3" ) 
	 ;;
    * ) 
        echo "Mauvaise valeur saisie"
        selectLampConfig ;;
esac 
}

selectdomain() {
    read -e -i "$msehrDom" -p "Choix du domaine [défaut : msehr.local] : " input
    msehrDom="${input:-$msehrDom}"
}

certGen() {
    mkdir /etc/ssl/$msehrDom
    cd /etc/ssl/$msehrDom
    openssl genrsa -out $msehrDom.key 4096
    openssl req -new -key $msehrDom.key -out $msehrDom.csr
    openssl x509 -req -days 3650 -in $msehrDom.csr -signkey $msehrDom.key -out $msehrDom.pem
}

apacheConfig() {
    ## Configuration vhost http
    echo "<VirtualHost *:80>
        ServerName $msehrDom
        ServerAlias msehr ehr medshakeehr MedShakeEHR
        RedirectMatch     permanent ^(.*)$ https://$msehrDom\$1
    </VirtualHost>

    <VirtualHost *:443>
        ServerName $msehrDom
        ServerAlias msehr ehr medshakeehr MedShakeEHR
        DocumentRoot "$msehrPath/public_html"
        RewriteEngine On
        SSLEngine On
        SSLCertificateFile /etc/ssl/$msehrDom/$msehrDom.pem
        SSLCertificateKeyFile /etc/ssl/$msehrDom/$msehrDom.key
        <Directory "$msehrPath/public_html">
            Options FollowSymLinks
            AllowOverride all
            Require all granted
        </Directory>
        ErrorLog /var/log/apache2/error.$msehrDom.log
        CustomLog /var/log/apache2/access.$msehrDom.log combined
    </VirtualHost>
    " > /etc/apache2/sites-available/$msehrDom.conf

    a2enmod rewrite headers ssl

    a2dissite 000-default.conf default-ssl.conf

    a2ensite $msehrDom 

    ## Réglage php.ini
	vphp=$(php -r "echo PHP_VERSION;" | cut -c1-3)
	sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 20M/' /etc/php/$vphp/apache2/php.ini
	sed -i 's/post_max_size = 8M/post_max_size = 20M/' /etc/php/$vphp/apache2/php.ini
	sed -i 's/;max_input_vars = 1000/max_input_vars = 10000/' /etc/php/$vphp/apache2/php.ini

    service apache2 restart
}   

mariadbConfig() {
    while true; do
        read -s -r -p "Choix du mot de passe administrateur (root) de la base de données (ne pas utiliser les caractères : \"'$,[]*?{}~#%\<,>|^; ) : " mysqlRootPswd
        echo
        read -s -r -p "Confirmation du mot de passe : " mysqlRootPswd1
        [ "$mysqlRootPswd" = "$mysqlRootPswd1" ] && break || echo "Essayez encore"
        echo
    done
    echo
    read -e -i "$msehrDbName" -p "Choix du nom de la base de donnée (défaut : medshakeehr) : " input
    msehrDbName="${input:-$msehrDbName}"
    echo
    read -p "Choix du nom de l'utilisateur de la base de données : " mysqlUser
    echo
    while true; do
        read -s -r -p "Choix du mot de passe utilisateur de la base de données (ne pas utiliser les caractères : \"'$,[]*?{}~#%\<,>|^; ) : " mysqlUserPswd 
        echo
        read -s -r -p "Confirmation du mot de passe : " mysqlUserPswd1
        echo
        [ "$mysqlUserPswd" = "$mysqlUserPswd1" ] && break || echo "Essayez encore"
    done
    service mysql start
    mysql <<EOF
    SET PASSWORD FOR 'root'@'localhost' = PASSWORD('${mysqlRootPswd}');
    DELETE FROM mysql.user WHERE User='';
    DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
    CREATE DATABASE $msehrDbName;
    GRANT ALL ON $msehrDbName.* TO '${mysqlUser}'@'localhost' IDENTIFIED BY '${mysqlUserPswd}' WITH GRANT OPTION;
    FLUSH PRIVILEGES;
EOF
}

selectVersion() {
    read -e -i "$selectVersion" -p "Vous voulez installer la dernière version stable tapez 1 [défaut], vous voulez installer une autre version tapez 2, ne rien installer tapez 3 : " input
    selectVersion="${input:-$selectVersion}"
    case $selectVersion in
    "1" )
        msehrLatest ;;  
    "2" )
        selectMsehrVersion ;;
    "3" )
        selectRemoveInstallFiles ;;
    * ) 
        echo "Mauvaise valeur saisie"
        selectVersion ;;
esac 
}

msehrLatest() {
    vRelease=$(curl --silent "https://api.github.com/repos/MedShake/MedShakeEHR-base/releases/latest" |
        grep '"tag_name":' |                                                          
        sed -E 's/.*"([^"]+)".*/\1/')
        msehrInstall
}	

selectMsehrVersion() {
    read -p "Tapez la version sous la forme vX.X.X : " vRelease
    msehrInstall
}	

msehrInstall() {
    wget --no-check-certificate https://github.com/MedShake/MedShakeEHR-base/archive/$vRelease.zip -P /tmp

    unzip -q -o -d /tmp /tmp/$vRelease.zip 

    mkdir -p $msehrPath/public_html
    version=$(echo $vRelease | cut -f2 -d "v")
    mv -f /tmp/MedShakeEHR-base-$version/* $msehrPath
    
    chown www-data:www-data -R $msehrPath
    chmod 755 $msehrPath $msehrPath/public_html

    cd $msehrPath
    su www-data -s/bin/bash -c 'composer install --no-interaction --no-cache -o'
    cd $msehrPath/public_html
    su www-data -s/bin/bash -c 'composer install --no-interaction --no-cache -o'
    echo "$msehrPath
    " > $msehrPath/public_html/MEDSHAKEEHRPATH
    su www-data -s/bin/bash -c  "php $msehrPath/public_html/install.php -N -s localhost -d $msehrDbName -u $mysqlUser -p $mysqlUserPswd -r https -D $msehrDom"

    selectRemoveInstallFiles
}  

selectRemoveInstallFiles() {
read -e -i "$selectRemove" -p "Si vous souhaitez détruire les fichiers d'installation tapez 1 [défaut], si vous souhaitez les conserver tapez 2 : " input
selectRemove="${input:-$selectRemove}"
case $selectRemove in
    "1" )
        removeInstallFiles ;;
    "2" ) 
	    echo "Pensez à configurer votre pare-feu et les mises à jours, plus d'infos sur https://c-medshakeehr.fr/doc.";;
    * ) 
        echo "Mauvaise valeur saisie"
        selectRemoveInstallFiles ;;
esac
}
    
removeInstallFiles() {
    rm -r /tmp/$vRelease.zip /tmp/MedShakeEHR-base-$version /tmp/debian-bash-installer.sh
    echo "Pensez à configurer votre pare-feu et les mises à jours, plus d'infos sur https://c-medshakeehr.fr/doc"
}

selectInstall(){
    echo "Bienvenue, ce script va vous guider lors de l'installation de MedShakeEHR. Si vous avez besoin d'aide au cours de l'installation : https://c-medshakeehr.fr/doc"
    read -e -i "$persoInstall" -p "Pour commencer, si vous souhaitez installer MedShakeEHR avec ses valeurs par défaut, tapez 1 [défaut] ou personnaliser l'installation, tapez 2 : " input
    persoInstall="${input:-$persoInstall}"
    case $persoInstall in
        "1" )
            packagesInstall
            certGen
            apacheConfig
            mariadbConfig
            msehrLatest ;;
        "2" )
            selectMsehrPath
            selectPackages
            selectLampConfig
            selectVersion ;;
        * ) 
            echo "Mauvaise valeur saisie"
            selectInstall ;;    
    esac  
}

# Variables globales par défauts.
persoInstall=1
msehrPath=/opt/ehr
selectInstall=1
selectLampConfig=1
msehrDom=msehr.local
msehrDbName=medshakeehr
selectVersion=1
selectRemove=1
msehrDep="apache2 composer curl ghostscript git imagemagick mariadb-server ntp pdftk php php-curl php-gd php-imagick php-imap php-intl php-mysql php-soap php-xml php-yaml php-zip"
extraDicom="orthanc"

clear
selectInstall