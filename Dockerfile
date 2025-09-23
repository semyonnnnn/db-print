FROM php:8.3-apache-bookworm

# 1. Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    gnupg \
    unixodbc \
    unixodbc-dev \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip \
    && rm -rf /var/lib/apt/lists/*

# 2. Add Microsoft repository and install ODBC driver
RUN curl -sSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor -o /usr/share/keyrings/microsoft.gpg && \
    echo "deb [signed-by=/usr/share/keyrings/microsoft.gpg] https://packages.microsoft.com/debian/12/prod bookworm main" > /etc/apt/sources.list.d/mssql-release.list && \
    apt-get update && \
    ACCEPT_EULA=Y apt-get install -y msodbcsql18

# 3. Install PHP SQLSRV extensions (needs unixodbc-dev already installed)
RUN pecl install sqlsrv pdo_sqlsrv && \
    docker-php-ext-enable sqlsrv pdo_sqlsrv

# 4. Enable Apache rewrite
RUN a2enmod rewrite

# 5. Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 6. Set working directory
WORKDIR /var/www/html

# 7. Copy project files
COPY . .
# 8. Install PHP dependencies and generate autoload
# RUN composer install --no-interaction --optimize-autoloader

COPY composer.json ./
RUN composer install --no-interaction --optimize-autoloader
COPY . .