## Prérequis
- Avoir [VirtualBox](https://www.virtualbox.org/wiki/Downloads), [Ansible](https://docs.ansible.com/ansible/latest/installation_guide/intro_installation.html) > 2.9 et [Vagrant](https://www.vagrantup.com/docs/installation) de configuré sur votre machine.
- Crée dans un but de démo ou de développement, ne pas utiliser en production avec des données réelles sans ajouter des paramètres de sécurité (mot de passe fort, contrôle d'accès).

## Installation 
- Cloner le projet.
- Ouvrir un terminal à la racine du projet.
- Configurer le fichier `secrets.yml` à la racine du projet pour personnaliser les infos et mots de passes :

```bash
cp secrets-sample.yml secrets.yml
nano secrets.yml
```

- Taper la commande suivante `vagrant up`.
- A la fin de l'exécution de la commande, ouvrir le navigateur se rendre à l'adresse suivante `http://55.55.55.5/install.php`.
- Vous pouvez finir la configuration de MedShakeEHR.
- Dans l'écran de configuration rapide cochez la case : `Ne pas créer la base de donnée`
- Le nom d'utilisateur et le mot de passe utilisateur correspondent à ce que vous avez choisi pour les variables `sqlUserAccount:` et `sqlUserPassword:`
- [Documentation de MedShakeEHR](https://www.logiciel-cabinet-medical.fr/documentation-technique/)

## Modifications de la configuration
- Pour arrêter la machine virtuelle taper `vagrant halt`.
- Pour détruire les fichiers de la machine virtuelle taper `vagrant destroy`.
- Vous pouvez modifier, les caractéristiques (ip, nombre de CPU, RAM, nom, distribution ...etc) de votre machine virtuelle dans `Vagrantfile`. Par exemple utiliser un `public network`
- Vous pouvez modifier l'approvisionnement de la machine virtuelle dans le fichier `main.yml`.
- Vous pouvez modifier les variables non sensibles dans le fichier `vars.yml`
- Vous pouvez modifier la configuration d'Apache via les fichiers de configurations placés dans le dossier `templates`.
- Pour réapprovisionner la machine virtuelle avec les nouveaux paramètres taper `vagrant provision`.