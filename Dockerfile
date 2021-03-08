FROM php:7.3-apache
RUN apt-get update && \
    apt-get install -y \
        git \
        libzip-dev \
        zlib1g-dev 
WORKDIR /tls
RUN openssl req -new -x509 -days 365 -nodes \
  -out server.cert \
  -keyout server.key \
  -subj "/C=RO/ST=Bucharest/L=Bucharest/O=IT/CN=www.example.ro"
RUN docker-php-ext-install mysqli pdo pdo_mysql zip mbstring bcmath
RUN a2enmod rewrite
RUN a2enmod ssl
RUN a2enmod headers
WORKDIR /install
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
ADD . /app
WORKDIR /app
RUN php /install/composer.phar require lcobucci/jwt:3.3.3
RUN php /install/composer.phar update
RUN php /install/composer.phar install
COPY site.conf /etc/apache2/sites-enabled/site.conf
RUN chown -R www-data:www-data /app
EXPOSE 443
