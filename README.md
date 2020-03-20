**Les informations ci-dessous sont une rapide introduction !
Consultez [www.logiciel-cabinet-medical.fr](http://www.logiciel-cabinet-medical.fr/) pour toutes les informations sur le logiciel MedShakeEHR !**

Un [groupe de discussion](https://groups.google.com/forum/#!forum/medshakeehr) est disponible pour les questions techniques.

# MedShakeEHR-base
Base pour MedShakeEHR, logiciel modulaire, universel, open source pour les praticiens santé.

## Avertissements
Ce logiciel ne doit pas être utilisé en l'état pour la stockage de données patient sur un réseau ouvert.  
Son utilisation doit être exclusivement limitée à un réseau privé sans utilisateur potentiellement hostile.
Il est livré ici sans aucune garantie, conformément à la licence GPL v3.

## Utilisation
MedShakeEHR-base constitue le coeur commun du logiciel. Il peut être accompagné de l'un ou l'autre de ses modules pour fonctionner de façon optimale.

## Installation
MedShakeEHR fonctionne sur un serveur xAMP.  

L'installation et la configuration complète du logiciel sont documentées sur le site [www.logiciel-cabinet-medical.fr](http://www.logiciel-cabinet-medical.fr/) à la rubrique [Documentation technique](http://www.logiciel-cabinet-medical.fr/documentation-technique/).

## Docker Image Build / Construction d'image Docker

WARNING : The docker configuration requires various environment variables to be set (server name, password ...). If you decided not to follow the procedure, make sure to set the expected environement variables (cf. the docker-compose.yml)
ATTENTION : La configuration Docker requiert le positionnement de variables d'environements (nom de serveur, mot de passe ...). Si vous décidez de ne pas suivre la procédure, assurez-vous de positionner ces variables (cf. docker-compose.yml).

1- Install Git and Docker as per their documentation for your platform. For instance on Ubuntu :
1- Installer GIT & Docker selon les instruction de votre plateforme. Par exemple sous Ubuntu :

>sudo apt install git docker.io docker-compose

NOTE : The version on the Ubuntu repository are usually a bit old. If you experience issues, check with the latest release.
NB: Les version disponibles danes les dépots Ubuntu sont souvent anciens. Si vous rencontrez des difficutlés, vérifiez avec des versions plus récentes.

2- Fetch/clone the desired version of EHR that is Docker compatible (here the latest):
2- Récupérer la version souhaitée de EHR qui soit compatible Docker (ici la dernière):

>git clone https://github.com/MedShake/MedShakeEHR-base.git

Until the pull request #32 is not done, you shall get instead :
Tant que la demande d'intégration #32 n'est pas actée, vous devez effectuer à la place :

>git clone https://github.com/bugeaud/MedShakeEHR-base.git

3- Enter the folder
3- Entrer dans le répertoire

>cd MedShakeEHR-base

4- Define (export) the required environement variables. Only EHR_SERVER_NAME is mandatory to be set as the real FQDN for the hostname.
4- Definir (export) les variables d'environement requises. Seul EHR_SERVER_NAME doit obligatoirement être positionée.

  - EHR_SERVER_NAME  : Nom du server / Name of the server
  - EHR_SERVER_ALIAS : Alias web / Web alias
  - EHR_DBMS_NAME : Nom de la base / Name of the database
  - EHR_DBMS_ROOT_PASSWORD : Mot de passe root de la base / Root password for the database
  - EHR_DBMS_USER_NAME :  Nom de l'utilisateur applicatif de la base / Database application user name
  - EHR_DBMS_USER_PASSWORD : Mot de passe de l'utilisateur applicatif de la base / Database application user name's password
  - EHR_DBMS_VAR : Some random data used at the dbms level
  - EHR_FINGERPRINT : Some random data used at the web level

5- Prepare the local filesystem to store the user data
5- Prepare le système de fichier local pour le stocker des données utilisateurs

> ./docker-ehr-prepare.sh

6- Initialize legacy configuration files with environement variables set :
6- Initialiser les fichiers de configuration historique à l'aide des valeur des variables d'environement positionnés :

> . docker-ehr-config.sh

NOTE : The dot and space before the command are mandatory to enable the spreading of the environement variables. Please save all the generated data at a proper location to enable a potenti$
NB: Le point et l'espace avant la commande sont obligatoires pour permettre la bonne propagation des variables d'environements. Veuillez sauvegarder toutes les données générées à un endroi$

7- Build the required EHR images :
7- Construire les images de EHR :

> ./docker-ehr-build.sh

8- Launch the application (create + start)
8- Lancer l'application (create + start)

> ./docker-compose up

9- An EHR instance is ready :
9- Vous avez une instance de EHR de disponible :

>http://$EHR_SERVER_NAME/

Follow the instructions there ...
Y suivre les instructions ...

## Docker Compose

A ready to use Docker Compose setup is propose with the following containers :
Une configuration Docker Compose prête à l'emploi est proposée qui dispose des conteneurs suivants :
- db : the DBMS for EHR / le SGBD de EHR
- web : the web front / le frontal web

An additional myadmin container can be enabled to perform some database maintenance duty.
Un conteneur myadmin additionel peut être activé pour effectuer des tâches de maintenances sur la base.

To start the containers
Pour démarrer les conteneurs

> docker-compose start

To remove the containers (this will not remove ~ehr/data)
Pour supprimer les conteneurs (ne supprime pas ~ehr/data)

> docker-compose rm

To list the containers :
Pour lister les conteneurs :

> docker-compose ps

From another terminal, access the "web" container's shell (here the db) :
Depuis un autre terminal, accèder à l'invite du conteneur "web" (ici le db)

> docker exec -it medshakeehrbase_web_1 /bin/bash

NOTE : most changes on files  will not be retained if starting a new container fresh from the image.
NB : la plus part des changements sur des fichiers ne seront pas concervés si un nouveau conteneur est créé à partir de l'impage.

## Single container Install / Installation dans un conteneur unique

There is also an alpha Docker-in-Docker (dind) single container configuration existing thru the Dockerfile file.
Il existe également une installation Docker-in-Docker (dind) de disponible via le fichier Dockerfile.

To use it follow the docker's procedure, but at step 8 instead of launching docker-compose :
Pour l'utiliser, suivre la procédure pour docker, mais à l'étape 8 au lieu d'utiliser docker-compose :

> ./docker-ehr-single-build.sh

Then, you can run the dind setup :
Ensuite, vous pouvez lancer la configuration dind :

> ./docker-ehr-single-run.sh

This would launch a GNU screen with multiple screen : 1 for the dind's docker daemon, 1 for building  & docker-compose and 2 more for shells available for you.
Ceci lancera un GNU screen avec de multiples écrans : 1 pour le service docker de dind, 1 pour la construction & docker-compose et 2 extra comme invites disponibles pour vous.
