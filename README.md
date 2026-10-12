# Teste técnico ConectaSUS

## Requisitos

- PHP 8.3+
- Composer
- MySQL ou MariaDB

## Como executar

Para acessar a pasta do projeto, instalar as dependências do Composer e criar o arquivo de configuração .env, execute:

```bash
cd src
composer install
cp .env.example .env
```

Configure as credenciais do banco no arquivo `.env` e rode:

```dotenv
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=teste_tecnico_conectasus
DB_USERNAME=root
DB_PASSWORD=sua_senha
```

```bash
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Aplicação disponível em `http://127.0.0.1:8000`.

## Como testar

Os testes são escritos com Pest, para testes da API de pacientes e unidades de saúde, pasta `src` execute:

```bash
php artisan test --compact tests/Feature/Http/Controllers
```
