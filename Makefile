ARTISAN = docker compose exec app php artisan

start:
	docker compose start	

build:
	docker compose up -d --build

stop:
	docker compose down -v 	

migrate:
	$(ARTISAN) migrate

create-migration:
	$(ARTISAN) make:migration $(word 2, $(MAKECMDGOALS))

create-controller:
	$(ARTISAN) make:controller $(word 2, $(MAKECMDGOALS))

route-list:
	$(ARTISAN) route:list

create-model:
	$(ARTISAN) make:model $(word 2, $(MAKECMDGOALS))

create-factory:
	$(ARTISAN) make:factory $(name) --model=$(model)

create-seed:
	$(ARTISAN) make:seeder $(word 2, $(MAKECMDGOALS))

seed:
	$(ARTISAN) db:seed

create-test:
	$(ARTISAN) make:test $(word 2, $(MAKECMDGOALS))

test:
	$(ARTISAN) test

phpqa:
	docker compose exec app phpstan analyse app -c phpstan.neon --memory-limit=1G
	docker compose exec app phpcs --standard=phpcs.xml app
	docker compose exec app phpmd app text phpmd.xml

phpcbf:
	docker compose exec app phpcbf app

phpcs:
	docker compose exec app phpcs --standard=phpcs.xml app

phpstan:
	docker compose exec app phpstan analyse app -c phpstan.neon --memory-limit=1G

phpmd:
	docker compose exec app phpmd app text phpmd.xml