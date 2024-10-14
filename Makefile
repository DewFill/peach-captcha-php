deployment_dir  := ./deployment

build-image:
	docker build -t peach-captcha .

start: build-image
	cd $(deployment_dir) && docker compose up -d
	cd $(deployment_dir) && docker compose exec site composer install

bash:
	cd $(deployment_dir) && docker compose exec site /bin/bash