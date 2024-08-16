.PHONY: setup snapshot tests
PHP = php

tests:
	$(PHP) vendor/bin/phpunit --testsuite units

test-all:
	$(PHP) vendor/bin/phpmd src/ text phpmd.xml
	$(PHP) vendor/bin/psalm -c psalm.xml --no-cache
	$(PHP) vendor/bin/phpunit --testsuite units

infection:
	$(PHP) vendor/bin/infection --threads=2
