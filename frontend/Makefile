# Default image and container names (override with `make build IMAGE_NAME=my-app`)
IMAGE_NAME ?= yuasa-battery-fe
CONTAINER_NAME ?= yuasa-battery-fe
PORT ?= 8080

.PHONY: help build run stop logs clean compose-up compose-down compose-logs

help:
	@echo "Available targets:"
	@echo "  make build            Build Docker image (IMAGE_NAME=$(IMAGE_NAME))"
	@echo "  make run              Run container in detached mode (PORT=$(PORT))"
	@echo "  make stop             Stop and remove running container"
	@echo "  make logs             Tail container logs"
	@echo "  make clean            Remove image and container"
	@echo "  make compose-up       docker compose up (build + run)"
	@echo "  make compose-down     docker compose down"
	@echo "  make compose-logs     docker compose logs -f"

build:
	docker build -t $(IMAGE_NAME) .

run:
	docker run -d --name $(CONTAINER_NAME) -p $(PORT):80 $(IMAGE_NAME)

stop:
	-@docker stop $(CONTAINER_NAME) >/dev/null 2>&1 || true
	-@docker rm $(CONTAINER_NAME) >/dev/null 2>&1 || true

logs:
	docker logs -f $(CONTAINER_NAME)

clean: stop
	-@docker rmi $(IMAGE_NAME) >/dev/null 2>&1 || true

compose-up:
	IMAGE_NAME=$(IMAGE_NAME) CONTAINER_NAME=$(CONTAINER_NAME) PORT=$(PORT) docker compose up -d --build

compose-down:
	IMAGE_NAME=$(IMAGE_NAME) CONTAINER_NAME=$(CONTAINER_NAME) PORT=$(PORT) docker compose down

compose-logs:
	IMAGE_NAME=$(IMAGE_NAME) CONTAINER_NAME=$(CONTAINER_NAME) PORT=$(PORT) docker compose logs -f
