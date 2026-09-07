FROM php:8.4-fpm-alpine

# Instala dependências do sistema e bibliotecas necessárias para as extensões
RUN apk add --no-cache \
    bash \
    git \
    icu-dev \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    linux-headers

# Instala extensões PHP necessárias para Symfony e PostgreSQL
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    intl \
    opcache \
    zip

# Instala o Composer a partir da imagem oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuração de diretório de trabalho
WORKDIR /var/www/html

EXPOSE 9000
CMD ["php-fpm"]