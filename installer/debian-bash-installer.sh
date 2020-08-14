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

# Choix des variables globales
echo "Bienvenue, l'installation de MedShakeEHR peut commencer. Si vous avez besoin d'aide au cours de l'installation, https://c-medshakeehr.fr/doc"
echo -e "Choix du dossier d'installation (ex: /home/ehr) : \c"
read msehrPath
echo -e "Choix du domaine (ex: msehr.local) : \c"
read msehrDom

# Installer les paquets
echo "Installation de MedShakeEHR minimale, tapez 1 [defaut]"
echo "Installation de MedShakeEHR avec Orthanc (Phonecapture, Echographe ...), tapez 2"
read selectinstall
msehrdep="ntp apache2 php mariadb-server ghostscript imagemagick pdftk git curl composer php-gd php-intl php-curl php-zip php-xml php-imagick php-imap php-soap php-mysql php-yaml"
extradicom="orthanc"
if [$selectinstall=2]
then 
	apt update && apt upgrade -y && apt install -y $msehrdep $extradicom
else
	apt update && apt upgrade -y && apt install -y $msehrdep
fi

# Génération d'un certificat ssl
mkdir /etc/ssl/$msehrDom
cd /etc/ssl/$msehrDom
openssl genrsa -out $msehrDom.key 2048
openssl req -new -key $msehrDom.key -out $msehrDom.csr
openssl x509 -req -days 3650 -in $msehrDom.csr -signkey $msehrDom.key -out $msehrDom.crt

# Configuration d'Apache
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
		### vphp à adapter si autre que Debian 10
	vphp=7.3
	sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 20M/' /etc/php/$vphp/apache2/php.ini
	sed -i 's/post_max_size = 8M/post_max_size = 20M/' /etc/php/$vphp/apache2/php.ini
	sed -i 's/max_input_vars = 1000/max_input_vars = 10000/' /etc/php/$vphp/apache2/php.ini

    ## Relancer Apache
service apache2 restart

# Configuration MariaDB
#mysql_secure_installation
echo -e "Choix du mot de passe root de mysql : /c"
read mysqlrootpass
echo -e "Choix du nom de l'admin de mysql : /c"
read mysqladmin
echo -e "Choix du mot de passe admin de mysql : /c"
read mysqlpass
mysql <<EOF
SET PASSWORD FOR 'root'@'localhost' = PASSWORD("${mysqlrootpass}");
DELETE FROM mysql.user WHERE User='';
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
FLUSH PRIVILEGES;
GRANT ALL ON *.* TO "${mysqladmin}"@'localhost' IDENTIFIED BY "${mysqlpass}" WITH GRANT OPTION;
EOF

# Installation de MedShakeEHR
	## Récupération de la dernière version de MedShakeEHR
	vlatest=$(curl --silent "https://api.github.com/repos/MedShake/MedShakeEHR-base/releases/latest" |
		grep '"tag_name":' |                                                          
		sed -E 's/.*"([^"]+)".*/\1/')
	wget --no-check-certificate https://github.com/MedShake/MedShakeEHR-base/archive/$vlatest.zip -P /tmp

	## Extraction du zip
	unzip -q -o -d /tmp /tmp/$vlatest.zip 

	## Création des répertoires de MedShakeEHR avec les bons droits
	mkdir -p $msehrPath/public_html
	latest=$(echo $vlatest | cut -f2 -d "v")
	mv -f /tmp/MedShakeEHR-base-$latest/* $msehrPath
	sed -i "1iSetEnv MEDSHAKEEHRPATH $msehrPath" $msehrPath/public_html/.htaccess

	## Réglages des bons droits utilisateurs
	chown www-data:www-data -R $msehrPath
	chmod 755 $msehrPath $msehrPath/public_html

	## Installation des dépendances avec composer
	cd $msehrPath
	su www-data -s/bin/bash -c 'composer install'
	cd $msehrPath/public_html
	su www-data -s/bin/bash -c 'composer install'
# Nettoyage
rm -r /tmp/$vlatest.zip /tmp/MedShakeEHR-base-$latest