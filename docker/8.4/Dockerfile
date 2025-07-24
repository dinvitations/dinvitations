FROM ubuntu:24.04

LABEL maintainer="Restu Edo Setiaji"

ARG APP_VERSION=unknown
ENV APP_VERSION=${APP_VERSION}

ARG WWWGROUP=1337
ARG NODE_VERSION=22
ARG POSTGRES_VERSION=17
ARG PHP_VERSION=8.4

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Add apt optimizations
RUN echo "Acquire::http::Pipeline-Depth 0;" > /etc/apt/apt.conf.d/99custom && \
    echo "Acquire::http::No-Cache true;" >> /etc/apt/apt.conf.d/99custom && \
    echo "Acquire::BrokenProxy true;" >> /etc/apt/apt.conf.d/99custom

# Base system deps
RUN apt-get update && apt-get upgrade -y && \
    mkdir -p /etc/apt/keyrings && \
    apt-get install -y --no-install-recommends \
        gnupg gosu curl ca-certificates zip unzip git \
        supervisor sqlite3 libcap2-bin libpng-dev \
        python3 dnsutils librsvg2-bin fswatch ffmpeg nano

# PHP repo
RUN curl -sS 'https://keyserver.ubuntu.com/pks/lookup?op=get&search=0xb8dc7e53946656efbce4c1dd71daeaab4ad4cab6' | \
    gpg --dearmor | tee /etc/apt/keyrings/ppa_ondrej_php.gpg > /dev/null && \
    echo "deb [signed-by=/etc/apt/keyrings/ppa_ondrej_php.gpg] https://ppa.launchpadcontent.net/ondrej/php/ubuntu noble main" > /etc/apt/sources.list.d/ppa_ondrej_php.list

# Node.js
RUN curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | \
    gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg && \
    echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_VERSION.x nodistro main" > /etc/apt/sources.list.d/nodesource.list

# Yarn
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | gpg --dearmor | \
    tee /etc/apt/keyrings/yarn.gpg >/dev/null && \
    echo "deb [signed-by=/etc/apt/keyrings/yarn.gpg] https://dl.yarnpkg.com/debian/ stable main" > /etc/apt/sources.list.d/yarn.list

# PostgreSQL client
RUN curl -sS https://www.postgresql.org/media/keys/ACCC4CF8.asc | gpg --dearmor | \
    tee /etc/apt/keyrings/pgdg.gpg >/dev/null && \
    echo "deb [signed-by=/etc/apt/keyrings/pgdg.gpg] http://apt.postgresql.org/pub/repos/apt noble-pgdg main" > /etc/apt/sources.list.d/pgdg.list

# Install PHP-FPM and extensions
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        php${PHP_VERSION}-fpm php${PHP_VERSION}-cli php${PHP_VERSION}-dev \
        php${PHP_VERSION}-pgsql php${PHP_VERSION}-sqlite3 php${PHP_VERSION}-gd \
        php${PHP_VERSION}-curl php${PHP_VERSION}-mongodb \
        php${PHP_VERSION}-imap php${PHP_VERSION}-mbstring \
        php${PHP_VERSION}-xml php${PHP_VERSION}-zip php${PHP_VERSION}-bcmath \
        php${PHP_VERSION}-soap php${PHP_VERSION}-intl php${PHP_VERSION}-readline \
        php${PHP_VERSION}-ldap php${PHP_VERSION}-msgpack php${PHP_VERSION}-igbinary \
        php${PHP_VERSION}-redis php${PHP_VERSION}-swoole \
        php${PHP_VERSION}-memcached php${PHP_VERSION}-pcov php${PHP_VERSION}-imagick \
        php${PHP_VERSION}-xdebug \
    && apt-get install -y --no-install-recommends \
        postgresql-client-$POSTGRES_VERSION \
        nodejs yarn \
        ghostscript imagemagick \
    && npm install -g npm pnpm bun \
    && curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    && chmod +x /usr/local/bin/composer \
    && apt-get -y autoremove && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Allow Imagick to read/write PDFs
RUN sed -i '/<policymap>/a \  <policy domain="coder" rights="read | write" pattern="PDF" />' /etc/ImageMagick-6/policy.xml

# Allow PHP to bind low ports
RUN setcap "cap_net_bind_service=+ep" /usr/sbin/php-fpm${PHP_VERSION}

# Add user
# RUN groupadd --force -g $WWWGROUP sail && \
#     useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u $WWWUSER sail
RUN groupmod -g $WWWGROUP www-data

# Add config files (you’ll provide these)
COPY start-container /usr/local/bin/start-container
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY php-fpm.conf /etc/php/${PHP_VERSION}/fpm/php-fpm.conf
COPY php.ini /etc/php/${PHP_VERSION}/fpm/conf.d/99-custom.ini

# Make sure it's executable
RUN chmod +x /usr/local/bin/start-container

EXPOSE 80

ENTRYPOINT ["start-container"]
