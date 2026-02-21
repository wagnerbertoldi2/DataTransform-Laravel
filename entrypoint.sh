#!/bin/bash
set -e

# Navega para o diretório de trabalho
cd /var/www/html

# 1. Instala dependências se o autoloader não existir
if [ ! -f "vendor/autoload.php" ]; then
    echo "Dependências não encontradas. Instalando..."
    composer install --no-interaction --no-dev --optimize-autoloader
fi

# 2. Garante que o arquivo .env existe
if [ ! -f ".env" ]; then
    echo "Criando arquivo .env..."
    cp .env.example .env
fi

# 3. Gera APP_KEY se estiver vazia
if ! grep -q "APP_KEY=base64:" .env; then
    echo "Gerando APP_KEY..."
    php artisan key:generate --force
fi

# 4. Inicializa o banco de dados SQLite se não existir
if [ ! -f database/database.sqlite ]; then
    echo "Criando banco de dados SQLite..."
    mkdir -p database
    touch database/database.sqlite
    chmod 664 database/database.sqlite
fi

# 5. Executa migrations
echo "Executando migrations..."
php artisan migrate --force

# 6. Executa seeders (dados base)
echo "Carregando dados base..."
php artisan db:seed --force

# 7. Inicia o servidor
echo "Servidor iniciado em http://localhost:8000"
exec php artisan serve --host=0.0.0.0 --port=8000