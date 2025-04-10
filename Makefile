up:
	sudo docker compose up -d

build:
	sudo docker compose up -d --build

chmod:
	chmod -R 777 /var/www

.PHONY: database

database:
	sudo docker exec -it chat_db mysql -u root -p

bash:
	sudo docker exec -it chat_app bash

user:
	php artisan moonshine:user

down:
	sudo docker compose down

op:
	php artisan optimize

mg:
	php artisan migrate

db:
	php artisan db:seed

reverb:
	php artisan reverb:start

queue:
	php artisan queue:listen

