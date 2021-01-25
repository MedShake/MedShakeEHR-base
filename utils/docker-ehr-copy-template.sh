#!/bin/bash
docker cp "$(docker-compose ps -q web)":/app/MedShakeEHR-base/templates/. ~/ehr/template/
chown -R www-data:www-data ~/ehr/template/
chmod -R 644 ~/ehr/template/
