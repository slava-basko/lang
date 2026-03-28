help:																			## Shows this help
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_\-\.]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

cs-fix:																			## Run PHP-CS-Fixer
	./php-cs-fixer.phar fix --allow-risky=yes

unit-tests:																		## Run phpunit
	XDEBUG_MODE=coverage ./phpunit.phar -c phpunit.xml --coverage-html tests/coverage/ --coverage-filter src/

check: cs-fix unit-tests
