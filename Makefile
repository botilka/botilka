DCO := docker-compose

php-cs-fixer.phar:
	wget https://cs.symfony.com/download/php-cs-fixer-v2.phar -O php-cs-fixer.phar
	chmod +x php-cs-fixer.phar

composer-require-checker.phar:
	wget https://github.com/maglnet/ComposerRequireChecker/releases/download/2.1.0/composer-require-checker.phar
	chmod +x composer-require-checker.phar

dkcip:
	@docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' $(shell $(DCO) ps -q ${SERVICE})

db:
	pgcli -h $(shell make SERVICE=postgres dkcip) -p 5432 -u user -d database
