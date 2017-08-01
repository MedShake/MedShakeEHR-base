**Les information ci dessous sont une rapide introduction purement technique ! 
Consultez [www.logiciel-cabinet-medical.fr](http://www.logiciel-cabinet-medical.fr/) pour toutes les informations sur le logiciel MedShakeEHR !**

# MedShakeEHR-base
Base pour MedShakeEHR, logiciel modulaire, universel, open source pour les praticiens santé.

## Avertissements
Ce logiciel ne doit pas être utilisé en l'état pour la stockage de données patient sur un réseau ouvert.  
Son utilisation doit être exclusivement limitée à un réseau privé sans utilisateur potentiellement hostile.
Il est livré ici sans aucune garantie, conformément à la licence GPL v3. 

## Utilisation
MedShakeEHR-base constitue le coeur commun du logiciel. Il doit être accompagné de l'un ou l'autre de ses modules pour fonctionner.
En particulier, il ne présente aucun template nécessaire à l'affichage. 

## Installation
MedShakeEHR fonctionne sur un serveur LAMP. Il a été testé sous Apache 2, PHP 7, Mysql 5.

Utiliser Composer (cf composer.json) pour l'installation des packages PHP nécessaires back-end et Bower (cf public_html/bower.json) pour les scripts front-end.  

Un dump MySQL est founi avec les éléments essentiels. Utiliser de préfèrence celui fourni avec le module que vous utiliserez.

L'ensemble du paramétrage de base (chemin, base de données, smtp ...) est à faire dans config/config.yml. 
Chaque paramètre peut être surchargé au niveau utilisateur dans l'interface de configuration.  

