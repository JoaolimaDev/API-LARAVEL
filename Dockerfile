# Use the official PHP image
FROM php:8.1-cli

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Set working directory
WORKDIR /var/www/html

# Copy the Laravel application files
COPY . .

# Expose port 3000
EXPOSE 3000

# Start PHP development server
CMD php artisan serve --host=0.0.0.0 --port=3000
