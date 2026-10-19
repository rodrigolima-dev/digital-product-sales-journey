FROM php:8.0.14

# Bullseye security packages moved to the Debian archive after EOL.
RUN sed -i -E 's#https?://(security|deb).debian.org/debian-security#https://archive.debian.org/debian-security#g' /etc/apt/sources.list \
    && apt-get -o Acquire::Check-Valid-Until=false update \
    && apt-get install -y libzip-dev libpq-dev

RUN docker-php-ext-install zip
RUN docker-php-ext-install sockets
RUN docker-php-ext-install pdo_pgsql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

