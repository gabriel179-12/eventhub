.PHONY: test test-filter

test:
	docker compose exec --user app -e APP_ENV=testing -e DB_DATABASE=eventhub_test api ./vendor/bin/pest

test-filter:
	docker compose exec --user app -e APP_ENV=testing -e DB_DATABASE=eventhub_test api ./vendor/bin/pest --filter=$(FILTER)