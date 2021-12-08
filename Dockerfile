FROM php:7.3-apache

# ==============================================================================
# Set up the machine
# ------------------------------------------------------------------------------
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

SHELL ["/bin/bash", "-o", "pipefail", "-c"]

RUN export DEBIAN_FRONTEND=noninteractive \
    && apt-get update && \
    apt-get install -y --no-install-recommends \
        git \
        libzip-dev \
        zlib1g-dev \
    && rm -rf /var/lib/apt/lists/*

RUN mkdir /tls && openssl req -new -x509 -days 365 -nodes \
  -out /tls/server.cert \
  -keyout /tls/server.key \
  -subj "/C=NL/ST=Overijssel/L=Enschede/O=PDS Interop/OU=IT/CN=pdsinterop.org"

RUN docker-php-ext-install \
    bcmath \
    mbstring \
    mysqli \
    pdo \
    pdo_mysql \
    zip

RUN a2enmod headers rewrite ssl

COPY site.conf /etc/apache2/sites-enabled/site.conf

WORKDIR /app

EXPOSE 443
# ==============================================================================


# ==============================================================================
# Add the source code
# ------------------------------------------------------------------------------
ARG PROJECT_PATH
RUN : "${PROJECT_PATH:=$PWD}"

COPY "${PROJECT_PATH}" /app/

RUN composer install --no-dev --prefer-dist
RUN chown -R www-data:www-data /app
# ==============================================================================
