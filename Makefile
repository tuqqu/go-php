bin_dir = vendor/bin

.PHONY: default
default: help

.PHONY: help
help:
	@echo "Usage: make [target]"
	@echo
	@echo "Targets:"
	@echo "  help     Show this help message"
	@echo "  test     Run all tests"
	@echo "  lint     Run linter"
	@echo "  cs-fix   Fix code style"
	@echo "  cs-check Run code style checker"
	@echo "  analyse  Run type checker"

.PHONY: cs-fix
cs-fix:
	$(bin_dir)/php-cs-fixer fix --diff --allow-risky=yes

.PHONY: cs-check
cs-check:
	$(bin_dir)/php-cs-fixer fix --dry-run --verbose --diff --using-cache=no --allow-risky=yes

.PHONY: analyse
analyse:
	$(bin_dir)/psalm --config=psalm.xml --no-cache

.PHONY: lint
lint: cs-check analyse

.PHONY: test-unit
test-unit:
	$(bin_dir)/phpunit --testsuite=unit

.PHONY: test-functional
test-functional:
	$(bin_dir)/phpunit --testsuite=functional

.PHONY: test
test: lint test-unit test-functional
