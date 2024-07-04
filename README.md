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
php artisan migrate --seed
```

A default user will be seeded with these credentials:

email: test@example.com<br/>
password: 123456

Run the project:

```sh
php artisan serve
```

**Login:**

`POST /api/v1/user/login`

```json
{
    "email": "test@example.com",
    "password": 123456
}
```

The api will respond with `user` and `token`.

```json
{
	"user": {
		"id": 2,
		"uuid": "9c5e57a3-855b-464e-97a4-df30a90bbf09",
		"first_name": "Test",
		"last_name": "User",
		"is_admin": 0,
		"email": "test@example.com",
		"email_verified_at": "2024-06-25T04:35:11.000000Z",
		"avatar": null,
		"address": "2816 Buckridge Brook Suite 706\nNew Koby, OR 45676-4522",
		"phone_number": "+17858275936",
		"is_marketing": 0,
		"created_at": "2024-06-25T04:35:11.000000Z",
		"updated_at": "2024-06-25T04:35:11.000000Z",
		"last_login_at": null
	},
	"token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3MTkyOTAxMTQsIm5iZiI6MTcxOTI5MDExNCwiZXhwIjoxNzE5MjkzNzE0LCJpc3MiOiJodHRwOi8vbG9jYWxob3N0IiwiYXVkIjoiaHR0cDovL2xvY2FsaG9zdDo1NDMyIiwianRpIjoiOWM1ZTU3YTctNzcwNy00OGUwLThhNjktZDJmMDI5ZDk4MGU0IiwidWlkIjoiOWM1ZTU3YTMtODU1Yi00NjRlLTk3YTQtZGYzMGE5MGJiZjA5IiwiaWlkIjoyfQ.bqZ2XGEpF-XDmV9jajpG5ksWxtbD7QbC5akJqxCyPFA"
}
```

The api documentation can be viewed at: http://localhost:8000/api/documentation
