FROM php:7.4-zts

RUN apt-get update && apt-get install -y \
    --no-install-recommends

RUN rm -rf /var/lib/apt/lists/*

RUN pecl install parallel-1.1.3 \
    && docker-php-ext-enable parallel

WORKDIR /app/examples

COPY ./src /app/src
COPY ./examples /app/examples

ENTRYPOINT ["php", "example.php"]

