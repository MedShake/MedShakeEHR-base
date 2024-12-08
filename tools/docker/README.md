#  MedShakeEHR Docker Compose

Pile LAMP pour MedShakeEHR en local :
* PHP
* Apache
* MySQL
* phpMyAdmin
* Orthanc
* Reverse proxy
* Certificat SSL autosigné
* VPN (Wireguard)

##  Installation

* Configurez le .env selon vos besoins.

```bash
cp sample.env .env
nano .env
```
*  Modifiez l'image msehr de votre choix. 

```bash
nano compose.yml
medshakeehr:
    image: marsante/msehrtest:x.x.x
```

* Ou modifiez le fichier compose avec le Dockerfile de votre choix. 

```bash
nano compose.yml
medshakeehr:
    build: ./
```

* Vous pouvez aussi modifier le Dockerfile avec votre clone de MedShakeEHR pour tester vos nouvelles fonctionnalités.
* Puis lancez la stack :
```bash
docker compose up --build -d
# sudo devant si docker non rootless et que l'utilisateur ne fait pas partie du groupe docker
# docker-compose up --build -d si vous avez une ancienne version de docker compose
```
suivant votre configuration.
* Tapez [msehr.localhost/install.php](msehr.localhost/install.php) dans votre navigateur.
* Suivez les instructions.

* Pour ajouter un module, ou le mettre à jour :

```bash
docker exec -ti msehr php /usr/local/bin/msehr.upgrade.php base
```

* les arguments disponibles sont : base, chiro, gyneco, general, thermal, mpr, osteo

## Personnalisation des paramètres PHP et MariaDB
* Décommettez les volumes correspondant dans le fichier `compose.yml` et passez leur vos fichiers personnalisés.
```yaml
# Pour PHP
- ./config/30-custom-php.ini:/usr/local/etc/php/30-custom-php.ini:ro
# Pour MariaDB
- ./config/custom-mariadb.cnf:/etc/mysql/conf.d/custom-mariadb.cnf:ro
```

## Orthanc
* Créez le fichier de configuration `cp sample-orthanc.json orthanc.json` et éditez `nano orthanc.json`
* Relancez la stack docker compose ainsi `docker compose --profile dicom`

## phpMyAdmin
* Relancez la stack docker compose ainsi `docker compose --profile debug` puis rendez-vous sur [pma.msehr.localhost/](pma.msehr.localhost/)

## VPN (Wireguard)
* Modifiez le .env en personnalisant avec vos données réseaux / domaine.
* Relancez la stack docker compose ainsi `docker compose --profile vpn`.

