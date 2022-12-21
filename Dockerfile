# We start from a Docker in Docker container
FROM docker:dind
ENV INITSYSTEM on

LABEL description="MedShake EHR All-In-One container"
LABEL maintainer="bugeaud@gmail.com"

# Set some environement variables required
ENV APP_PATH /app
ENV MEDSHAKEEHRPATH $APP_PATH/MedShakeEHR-base/
ENV SCREENDIR /root/ehr/screen/

# Create the application directory from the local
ADD . $MEDSHAKEEHRPATH
#WORKDIR $MEDSHAKEEHRPATH

# Create the mount point to avoid right errors
RUN mkdir -p $SCREENDIR -m 700

# Check system is fresh and clean
RUN apk update && apk upgrade

# Install OpenRC's Init & Python package manager
#RUN apk add screen  py-pip
RUN apk add py-pip screen

# Make sure PIP is up-to-date
RUN pip install --upgrade pip

# Install docker-compose package & supervisord
RUN pip install docker-compose supervisor

#RUN pwd

# Make sure the Docker is there as "service"
#RUN rc-update add docker boot

# See what is in RC Init
#RUN rc-status --all

# Then, actually, simply run it right now
#RUN service docker start

# Move to the application
WORKDIR $MEDSHAKEEHRPATH

#RUN dockerd --host=unix:///var/run/docker.sock --host=tcp://0.0.0.0:2375
# Config docker compose as a service $$$
RUN docker-compose config --services

# Create the sub-containers
#RUN docker-compose up --build -d
#RUN docker-compose up --build --no-start

# Create the sub-containers
#RUN docker-compose create

#COPY docker-compose-entrypoint.sh /usr/local/bin/
#ENTRYPOINT ["docker-compose-entrypoint.sh"]
#CMD []

RUN mkdir -p /var/log/docker-compose
COPY docker-compose-screenrc /etc/docker-compose-screenrc

# Document the exposed port. Note that 443 is for future use.
EXPOSE 80/tcp
EXPOSE 8080/tcp
#EXPOSE 3606/tcp
EXPOSE 443/tcp

VOLUME /root/ehr/
#VOLUME ~/ehr/screen

# Create the screen lauching a docker daemon and a docker compose from known UNIX socket location
ENTRYPOINT ["screen", "-c", "/etc/docker-compose-screenrc"]
