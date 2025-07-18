start:
	docker-compose start	

build:
	docker-compose up -d --build

stop:
	docker-compose down -v 	

migrate:
	docker-compose exec app php artisan migrate

create-migration:
	docker-compose exec app php artisan make:migration $(word 2, $(MAKECMDGOALS))

create-controller:
	docker-compose exec app php artisan make:controller $(word 2, $(MAKECMDGOALS))

route-list:
	docker-compose exec app php artisan route:list

create-model:
	docker-compose exec app php artisan make:model $(word 2, $(MAKECMDGOALS))

create-factory:
	docker-compose exec app php artisan make:factory $(name) --model=$(model)

create-seed:
	docker-compose exec app php artisan make:seeder $(word 2, $(MAKECMDGOALS))

seed:
	docker-compose exec app php artisan db:seed

phpqa:
	docker compose exec app phpstan analyse app
	docker compose exec app phpcs --standard=phpcs.xml app
	docker compose exec app phpmd app text phpmd.xml

phpcbf:
	docker compose exec app phpcbf app

phpcs:
	docker compose exec app phpcs --standard=phpcs.xml app

phpstan:
	docker compose exec app phpstan analyse app

phpmd:
	docker compose exec app phpmd app text phpmd.xml