# Petshop api

This is a project created using Laravel 11 for creating petshop api.

The project uses default sqlite database connection.


## Run the project

Create .env file:

```sh
cp .env.example .env
```

Install project dependencies:

```sh
composer install
```

Generate APP_KEY:

```sh
php artisan key:generate
```

Generate a JWT key and set the value in .env for `JWT_KEY` variable.

Run migrations and seeders:

```sh
php artisan migrate:fresh --seed
```

A default user will be seeded with these credentials:

email: test@example.com<br/>
password: 123456

Run the project:

```sh
php artisan serve
```

The api documentation can be viewed at: http://localhost:8000/api/documentation
