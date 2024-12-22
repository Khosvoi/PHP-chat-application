FROM php:8.4.1-cli
WORKDIR /public
COPY . .

RUN mkdir -p /var/lib/chat-db \
    && apt-get update && apt-get install -y \
    git \
    unzip \
    sqlite3 \
    && rm -rf /var/lib/apt/lists/* \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install \
    && sqlite3 /var/lib/chat-db/messagingDB.sqlite < src/DB/init.sql \
    && chown -R www-data:www-data /var/lib/chat-db \
    && chmod -R 777 /var/lib/chat-db

EXPOSE 8000
VOLUME ["/var/lib/chat-db"]
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]