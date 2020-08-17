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

# Choix dossier d'installation.
selectPath() {
    echo -e "Choix du dossier d'installation (ex: /home/ehr) : \c"
    read msehrPath
}

# Choix des paquets à installer
selectPackages() {
    echo "Installation des dépendances de MedShakeEHR minimales, tapez 1 [défaut]"
    echo "Installation de MedShakeEHR avec Orthanc (Phonecapture, Echographe ...), tapez 2"
    echo "Ne rien installer, tapez 3"
	echo -e "Choix : \c"
    read selectInstall
    case $selectInstall in
        "1" )
            msehrDep=$msehrMin 
            packagesInstall ;;
        "2" )
            msehrDep="${msehrMin} ${extraDicom}"
            packagesInstall ;;
        "3" ) 
		 ;;
    esac 
}

# Installer les paquets
packagesInstall(){
	apt update && apt upgrade -y && apt install -y $msehrDep
}

# Choix de configurer le serveur LAMP
selectLamp() {
echo -e "Vous souhaitez que le serveur LAMP soit configuré par défaut, tapez 1, vous voulez configurer le serveur LAMP tapez 2, si vous l'avez déjà configuré tapez 3 : \c"
read selectLamp
case $selectLamp in
    "1" )
        certGen
        apacheInstall
        mariadbInstall ;;       
    "2" )
        selectdomain
        certGen
        apacheInstall
        mariadbInstall ;;
    "3" ) 
	 ;;
esac 
}

# Choix du certificat
selectdomain() {
    echo -e "Choix du domaine (ex: msehr.local) : \c"
    read msehrDom
}

# Génération d'un certificat ssl
certGen() {
    mkdir /etc/ssl/$msehrDom
    cd /etc/ssl/$msehrDom
    openssl genrsa -out $msehrDom.key 2048
    openssl req -new -key $msehrDom.key -out $msehrDom.csr
    openssl x509 -req -days 3650 -in $msehrDom.csr -signkey $msehrDom.key -out $msehrDom.crt
}

# Configuration d'Apache
apacheInstall() {
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

    ## Activation des modules Apache
    a2enmod rewrite headers ssl

    ## Désactivation des sites par défaut Apache
    a2dissite 000-default.conf default-ssl.conf

    ## Activation du vhost
    a2ensite $msehrDom $msehrDom-ssl

    ## Réglage php.ini
	vphp=$(php -r "echo PHP_VERSION;" | cut -c1-3)
	sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 20M/' /etc/php/$vphp/apache2/php.ini
	sed -i 's/post_max_size = 8M/post_max_size = 20M/' /etc/php/$vphp/apache2/php.ini
	sed -i 's/max_input_vars = 1000/max_input_vars = 10000/' /etc/php/$vphp/apache2/php.ini

    ## Relancer Apache
    service apache2 restart
}   

# Configuration MariaDB
mariadbInstall() {
    echo -e "Choix du mot de passe root de mysql : \c"
    read mysqlrootpass
    echo -e "Choix du nom de l'admin de mysql : \c"
    read mysqladmin
    echo -e "Choix du mot de passe admin de mysql : \c"
    read mysqlpass
    mysql <<EOF
    SET PASSWORD FOR 'root'@'localhost' = PASSWORD("${mysqlrootpass}");
    DELETE FROM mysql.user WHERE User='';
    DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
    FLUSH PRIVILEGES;
    GRANT ALL ON *.* TO "${mysqladmin}"@'localhost' IDENTIFIED BY "${mysqlpass}" WITH GRANT OPTION;
EOF
}

# Choix de la version de MedShakeEHR
selectVersion() {
    echo -e "Vous voulez installer la dernière version stable tapez 1, vous voulez installer une autre version tapez 2 : \c"
    read selectv
    case $selectv in
    "1" )
        msehrLatest ;;  
    "2" )
        selectMsehrV ;;
esac 
}

# Récupération de la dernière version de MedShakeEHR
msehrLatest() {
    vversion=$(curl --silent "https://api.github.com/repos/MedShake/MedShakeEHR-base/releases/latest" |
        grep '"tag_name":' |                                                          
        sed -E 's/.*"([^"]+)".*/\1/')
}	

# Récupération d'une version de MedShakeEHR
selectMsehrV() {
    echo -e "Tapez la version sous la forme vX.X.X : \c"
    read vversion
}	

# Installation de MedShakeEHR     
msehrInstall() {
    wget --no-check-certificate https://github.com/MedShake/MedShakeEHR-base/archive/$vversion.zip -P /tmp

    ## Extraction du zip
    unzip -q -o -d /tmp /tmp/$vversion.zip 

    ## Création des répertoires de MedShakeEHR avec les bons droits
    mkdir -p $msehrPath/public_html
    version=$(echo $vversion | cut -f2 -d "v")
    mv -f /tmp/MedShakeEHR-base-$version/* $msehrPath
    sed -i "1iSetEnv MEDSHAKEEHRPATH $msehrPath" $msehrPath/public_html/.htaccess

    ## Réglages des bons droits utilisateurs
    chown www-data:www-data -R $msehrPath
    chmod 755 $msehrPath $msehrPath/public_html

    ## Installation des dépendances avec composer
    cd $msehrPath
    su www-data -s/bin/bash -c 'composer install'
    cd $msehrPath/public_html
    su www-data -s/bin/bash -c 'composer install'
}  

# Choix du nettoyage
selectRemoveInstallFiles() {
    echo -e "Si vous souhaitez détruire les fichiers d'installation tapez 1, si vous souhaitez les conserver tapez 2"
read selectRemove
case $selectRemove in
    "1" )
        removeInstallFiles ;;
    "2" ) 
	 ;;
esac
}
    
# Nettoyage
removeInstallFiles() {
    rm -r /tmp/$vversion.zip /tmp/MedShakeEHR-base-$version /tmp/debian-bash-installer.sh
}

# Variables globales par défauts.
msehrPath=/home/ehr
msehrDom=msehr.local
msehrMin="ntp apache2 php mariadb-server ghostscript imagemagick pdftk git curl composer php-gd php-intl php-curl php-zip php-xml php-imagick php-imap php-soap php-mysql php-yaml"
extraDicom="orthanc"
msehrDep=$msehrMin

# Sélection de l'installation.
echo " Bienvenue, ce script va vous guider lors de l'installation de MedShakeEHR. Si vous avez besoin d'aide au cours de l'installation : https://c-medshakeehr.fr/doc"
echo -e "Pour commencer, si vous souhaitez installer MedShakeEHR avec ses valeurs par défaut, tapez 1 ou personnaliser l'installation, tapez 2 : \c"
read persoInstall
case $persoInstall in
    "1" )
        packagesInstall
        certGen
        apacheInstall
        mariadbInstall
        msehrLatest
        msehrInstall
        removeInstallFiles ;;
    "2" )
        selectPath
        selectPackages
        selectLamp
        selectVersion
        msehrInstall
        selectRemoveInstallFiles ;;
esac  