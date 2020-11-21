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
    read -p "Choix du dossier d'installation (ex: /home/ehr) : " msehrPath
}

selectPackages() {
    echo "Installation des dépendances de MedShakeEHR minimales, tapez 1 [défaut]"
    echo "Installation de MedShakeEHR avec Orthanc (Phonecapture, Echographe ...), tapez 2"
    echo "Ne rien installer, tapez 3"
    read -p "Choix : " selectInstall
    case $selectInstall in
        "1" )
            msehrDep=$msehrDepMin 
            packagesInstall ;;
        "2" )
            msehrDep="${msehrDepMin} ${extraDicom}"
            packagesInstall ;;
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

selectLampConfig() {
read -p "Vous souhaitez que le serveur LAMP soit configuré par défaut, tapez 1, vous voulez configurer le serveur LAMP tapez 2, si vous l'avez déjà configuré tapez 3 : " selectLampConfig
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
    read -p "Choix du domaine (ex: msehr.local) : " msehrDom
}

certGen() {
    mkdir /etc/ssl/$msehrDom
    cd /etc/ssl/$msehrDom
    openssl genrsa -out $msehrDom.key 2048
    openssl req -new -key $msehrDom.key -out $msehrDom.csr
    openssl x509 -req -days 3650 -in $msehrDom.csr -signkey $msehrDom.key -out $msehrDom.crt
}

apacheConfig() {
    ## Configuration vhost http
    echo "<VirtualHost *:80>
        ServerName $msehrDom
        ServerAlias msehr ehr medshakeehr MedShakeEHR $msehrDom
        DocumentRoot "$msehrPath/public_html"
        <Directory "$msehrPath/public_html">
            Options FollowSymLinks
            AllowOverride all
            Require all granted
        </Directory>
        ErrorLog /var/log/apache2/error.$msehrDom.log
        CustomLog /var/log/apache2/access.$msehrDom.log combined
    </VirtualHost> 
    " > /etc/apache2/sites-available/$msehrDom.conf

    ## Configuration vhost https
    echo "<VirtualHost *:443>
        ServerName $msehrDom
        ServerAlias msehr ehr medshakeehr MedShakeEHR $msehrDom
        DocumentRoot "$msehrPath/public_html"
            SSLCertificateFile /etc/ssl/$msehrDom/$msehrDom.crt
            SSLCertificateKeyFile /etc/ssl/$msehrDom/$msehrDom.key
        <Directory "$msehrPath/public_html">
            Options FollowSymLinks
            AllowOverride all
            Require all granted
        </Directory>
        ErrorLog /var/log/apache2/error.$msehrDom.log
        CustomLog /var/log/apache2/access.$msehrDom.log combined
    </VirtualHost> 
        " >> /etc/apache2/sites-available/$msehrDom-ssl.conf

    a2enmod rewrite headers ssl

    a2dissite 000-default.conf default-ssl.conf

    a2ensite $msehrDom $msehrDom-ssl

    ## Réglage php.ini
	vphp=$(php -r "echo PHP_VERSION;" | cut -c1-3)
	sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 20M/' /etc/php/$vphp/apache2/php.ini
	sed -i 's/post_max_size = 8M/post_max_size = 20M/' /etc/php/$vphp/apache2/php.ini
	sed -i 's/;max_input_vars = 1000/max_input_vars = 10000/' /etc/php/$vphp/apache2/php.ini

    service apache2 restart
}   

mariadbConfig() {
    read -s -r -p "Choix du mot de passe administrateur (root) de la base de données : " mysqlRootPswd
    echo
    read -p "Choix du nom de l'utilisateur de la base de données : " mysqlUser
    read -s -r -p "Choix du mot de passe utilisateur de la base de données : " mysqlUserPswd 
    echo
    service mysql start
    mysql <<EOF
    SET PASSWORD FOR 'root'@'localhost' = PASSWORD("${mysqlrootpass}");
    DELETE FROM mysql.user WHERE User='';
    DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
    CREATE DATABASE medshakeehr;
    GRANT ALL ON medshakeehr.* TO "${mysqlUser}"@'localhost' IDENTIFIED BY "${mysqlUserPswd}" WITH GRANT OPTION;
    FLUSH PRIVILEGES;

EOF
}

selectMsehrVersion() {
    read -p "Vous voulez installer la dernière version stable tapez 1, vous voulez installer une autre version tapez 2, ne rien installer tapez 3 : " selectv
    case $selectv in
    "1" )
        msehrLatest ;;  
    "2" )
        selectMsehrVersion ;;
    "3" )
         ;;
    * ) 
        echo "Mauvaise valeur saisie"
        selectMsehrVersion ;;
esac 
}

msehrLatest() {
    vversion=$(curl --silent "https://api.github.com/repos/MedShake/MedShakeEHR-base/releases/latest" |
        grep '"tag_name":' |                                                          
        sed -E 's/.*"([^"]+)".*/\1/')
}	

selectMsehrVersion() {
    read -p "Tapez la version sous la forme vX.X.X : " vversion
}	

msehrInstall() {
    wget --no-check-certificate https://github.com/MedShake/MedShakeEHR-base/archive/$vversion.zip -P /tmp

    unzip -q -o -d /tmp /tmp/$vversion.zip 

    mkdir -p $msehrPath/public_html
    version=$(echo $vversion | cut -f2 -d "v")
    mv -f /tmp/MedShakeEHR-base-$version/* $msehrPath
    sed -i "1iSetEnv MEDSHAKEEHRPATH $msehrPath" $msehrPath/public_html/.htaccess

    chown www-data:www-data -R $msehrPath
    chmod 755 $msehrPath $msehrPath/public_html

    cd $msehrPath
    su www-data -s/bin/bash -c 'composer install --no-interaction -o'
    cd $msehrPath/public_html
    su www-data -s/bin/bash -c 'composer install --no-interaction -o'
}  

selectRemoveInstallFiles() {
read -p "Si vous souhaitez détruire les fichiers d'installation tapez 1, si vous souhaitez les conserver tapez 2" selectRemove
case $selectRemove in
    "1" )
        removeInstallFiles ;;
    "2" ) 
	 ;;
    * ) 
        echo "Mauvaise valeur saisie"
        selectRemoveInstallFiles ;;
esac
}
    
removeInstallFiles() {
    rm -r /tmp/$vversion.zip /tmp/MedShakeEHR-base-$version /tmp/debian-bash-installer.sh
}

selectInstall(){
    echo "Bienvenue, ce script va vous guider lors de l'installation de MedShakeEHR. Si vous avez besoin d'aide au cours de l'installation : https://c-medshakeehr.fr/doc"
    read -p "Pour commencer, si vous souhaitez installer MedShakeEHR avec ses valeurs par défaut, tapez 1 ou personnaliser l'installation, tapez 2 : " persoInstall
    case $persoInstall in
        "1" )
            packagesInstall
            certGen
            apacheConfig
            mariadbConfig
            msehrLatest
            msehrInstall
            removeInstallFiles ;;
        "2" )
            selectMsehrPath
            selectPackages
            selectLampConfig
            selectMsehrVersion
            msehrInstall
            selectRemoveInstallFiles ;;
        * ) 
            echo "Mauvaise valeur saisie"
            selectInstall ;;    
    esac  
}

# Variables globales par défauts.
msehrPath=/home/ehr
msehrDom=msehr.local
msehrDepMin="ntp apache2 php mariadb-server ghostscript imagemagick pdftk git curl composer php-gd php-intl php-curl php-zip php-xml php-imagick php-imap php-soap php-mysql php-yaml"
extraDicom="orthanc"
msehrDep=$msehrDepMin

clear
selectInstall