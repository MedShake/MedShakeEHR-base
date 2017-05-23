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

## Fonctions principales 
### Fonctions de gestion de base
- création / édition de dossiers patient
- recherche multicritère de patient et affichage de listing
- enregistrement des consultations, celles-ci pouvant être de différent type avec un questionnaire de recueil et un modèle d'impression de compte rendu spécifiques à chacune.
- mise en forme automatique des comptes redus des examens, en particulier d'imagerie avec liaison DICOM à l'appareil utilisé (voir plus bas)
- gestion et intégration au dossier patient de documents, soit en provenance d'un fichier PDF / TXT externe déposé par drag and drop, soit via récupération automatique d'une boite mail apicrypt.
- gestion des balises HPRIM à l'export (mail apicrypt) comme à l'import (indexation du document dans le bon dossier, présentation plus lisible de la biologie)
- rédaction d'ordonnances à partir de prescriptions types pré enregistrées (modifiables) avec impression ALD ou non.
- rédaction de courrier et certificat (modèles préétablis enregistrables à l'infini)
- gestion des règlements en fonction d'une liste d'actes préétablis (éditable)
- gestion d'une liste de professionnels correspondants
- envoi par mail depuis le dossier patient de n'importe quelle pièce générée (ordo, certificat ...) vers un correspondant apicrypt ou vers l'email du patient.
- impression propre de toutes les pièces générées (le logiciel génère du PDF : impression simple depuis le navigateur)
- historique des consultations et actes du jour, triable par type (consultation, courrier, ordonnance ...) offrant au clic une prévisualisation de chaque élément.
- mise en valeur dans l'historique d’éléments importants, titre de chaque élément personnalisable.
- Récapitulatif des paiements reçus par dates (bornes) et par type avec listing complet.
- Page d'enregistrement des paiements des patients du jour sans nécessité d'accès au dossier (encaissement par secrétaire)

### Fonctions liées à un appareil d'imagerie
Toutes les fonctions liées à un appareil d'imagerie (on pense en particulier aux échographes) se font de façon transparente via l'intermédiaire du [serveur DICOM Orthanc](http://www.orthanc-server.com/) (logiciel open source !).

- envoi de l'identité et des données du patient à l'appareil (fonction worklist)
- réception des dernières mesures du dernier examen pour intégration automatique au compte-rendu (fonction DICOM SR)
- visualisation de tous les examens d'un patient, images et mesures (données DICOM SR)
- constitution de PDF (ou de ZIP) à partir des images pour expédition par mail ou impression 

### Fonctions liées à Apicrypt
- envoi de mail avec pièce jointe à un correspondant apicrypt avec en-tête HPRIM
- réception de mail apicrypt avec pièce jointe et intégration automatique dans le bon dossier

NB : les fichiers nécessaires au chiffrage / déchiffrage apicrypt ne sont pas fournis ici.
