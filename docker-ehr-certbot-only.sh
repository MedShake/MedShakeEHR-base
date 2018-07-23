#!/bin/sh
# Let's generate some certificate in the EHR folder
# The server has to be accessible on standard HTTP (tcp/80) and HTTPS (tcp/443) ports.
# Once done,  make sure to backup & store in a safe place all the ~/ehr/decurity data.

docker run -it --rm --name certbot -v $HOME/ehr/security/tls/letsencrypt/etc:/etc/letsencrypt -v $HOME/ehr/security/tls/letsencrypt/var:/var/lib/letsencrypt certbot/certbot certonly $@

