# https://hub.docker.com/_/php
FROM php:8.2-apache-bookworm
ENV PHPSTAGE=production
ARG DEBIAN_FRONTEND=noninteractive
RUN set -ex; \
    \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        cron \
        ghostscript \
        git \
        mariadb-client \
        pdftk-java \
    ; \
    rm -rf /var/lib/apt/lists/*
RUN set -ex; \
    \
    savedAptMark="$(apt-mark showmanual)"; \
    \
    apt-get -y update;\
    apt-get install -y --no-install-recommends \
    libc-client-dev \
    libgpgme11-dev \
    libmagickwand-dev \
    libyaml-dev \
    libzip-dev \
    ; \
    pecl install gnupg \
    && \
    pecl install yaml \
    && \
    docker-php-ext-enable gnupg yaml \
    ; \
    docker-php-ext-configure gd \
		--with-freetype \
		--with-jpeg \
    ; \
    docker-php-ext-install -j$(nproc) \
    bcmath \
    gd \
    intl \
    pdo_mysql \
    soap \
    zip \
    && \
    # some misbehaving extensions end up outputting to stdout 🙈 (https://github.com/docker-library/wordpress/issues/669#issuecomment-993945967)
    out="$(php -r 'exit(0);')"; \
    [ -z "$out" ]; \
    err="$(php -r 'exit(0);' 3>&1 1>&2 2>&3)"; \
    [ -z "$err" ]; \
    \
    extDir="$(php -r 'echo ini_get("extension_dir");')"; \
    [ -d "$extDir" ]; \
    # reset apt-mark's "manual" list so that "purge --auto-remove" will remove all build dependencies
    apt-mark auto '.*' > /dev/null; \
    apt-mark manual $savedAptMark; \
    ldd "$extDir"/*.so \
        | awk '/=>/ { so = $(NF-1); if (index(so, "/usr/local/") == 1) { next }; gsub("^/(usr/)?", "", so); printf "*%s\n", so }' \
        | sort -u \
        | xargs -r dpkg-query --search \
        | cut -d: -f1 \
        | sort -u \
        | xargs -rt apt-mark manual; \
    \
    apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false; \
    rm -rf /var/lib/apt/lists/*; \
    \
    ! { ldd "$extDir"/*.so | grep 'not found'; }; \
    # check for output like "PHP Warning:  PHP Startup: Unable to load dynamic library 'foo' (tried: ...)
    err="$(php --version 3>&1 1>&2 2>&3)"; \
    [ -z "$err" ]; \
    rm -r /tmp/pear; \
    a2enmod rewrite headers ssl; \
    mv "$PHP_INI_DIR/php.ini-$PHPSTAGE" "$PHP_INI_DIR/php.ini"

COPY config/30-custom-php.ini "$PHP_INI_DIR/conf.d" 
COPY --from=docker.io/composer:2.8 /usr/bin/composer /usr/local/bin/composer
COPY config/vhost-docker /etc/apache2/sites-available/000-default.conf
ENV VRELEASE=master
RUN curl -fsSL -o /tmp/msehr.tar.gz https://github.com/MedShake/MedShakeEHR-base/archive/"$VRELEASE".tar.gz && \
mkdir /usr/src/medshakeehr && \
tar -xf /tmp/msehr.tar.gz -C /usr/src/medshakeehr --strip-components=1 && \
rm /tmp/msehr.tar.gz
VOLUME /var/www/html/
COPY msehr.entrypoint /usr/local/bin/
COPY msehr.upgrade.php /usr/local/bin/
ENTRYPOINT ["msehr.entrypoint"] 
CMD ["apache2-foreground"]