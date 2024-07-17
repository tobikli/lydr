## vendor
FROM composer:2.4 AS vendor
WORKDIR /app/
COPY --chown=www-data composer.json composer.lock /app/
RUN composer install \
    --ignore-platform-reqs \
    --no-ansi \
    --no-dev \
    --no-interaction \
    --no-scripts
 # vendor

FROM wordpress:php8.0-apache AS test

WORKDIR /var/www/html

RUN apt update && apt install -y less unzip curl sudo subversion mariadb-client \
    && apt-get autoclean \
    && chsh -s /bin/bash www-data && \
    pecl install xdebug-3.1.5 2>/dev/null && \
    docker-php-ext-enable xdebug

COPY --from=wordpress:cli /usr/local/bin/wp /usr/local/bin/wp
COPY --from=vendor /usr/bin/composer /usr/bin/composer

# Create testing environment
COPY --chmod=755 bin/install-wp-tests.sh /usr/local/bin/
RUN su www-data -c "/usr/local/bin/install-wp-tests.sh wordpress_test wordpress_test 'wordpress_test' mysql latest true" \
    && echo "*** install-wp-tests installed"

# -----------------------------------------------
FROM test AS dev
RUN su www-data -c "/usr/local/bin/install-wp-tests.sh wordpress_test wordpress_test 'wordpress_test' db latest true" \
    && echo "*** install-wp-tests installed"

# Code coverage
RUN pecl install xdebug
