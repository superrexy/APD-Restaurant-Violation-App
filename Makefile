.PHONY: start-backend start-yolo start-frontend start-all stop

# Start Laravel Reverb server
start-websocket:
	@echo "Starting backend (Laravel Reverb)..."
	cd backend && php artisan reverb:start

start-backend:
	@echo "Starting backend..."
	@echo "=================================="
	@echo "Backend:    http://localhost:8000"
	@echo "=================================="
	@echo ""
	cd backend && php artisan serve

# Start YOLO detection service
start-yolo:
	@echo "Starting YOLO service..."
	cd yolo-service && source .venv/bin/activate && python main.py

# Start Frontend dev server
start-frontend:
	@echo "Starting frontend (Vite)..."
	cd frontend && bun dev

# Start all services in parallel
start-all:
	@echo "Starting all services for end-to-end testing..."
	@echo "=================================="
	@echo "Backend:    http://localhost:8000"
	@echo "Reverb:     ws://localhost:8080"
	@echo "YOLO:       http://localhost:8081"
	@echo "Frontend:   http://localhost:3000"
	@echo "=================================="
	@echo ""
	cd backend && php artisan reverb:start &
	cd backend && php artisan queue:work &
	cd yolo-service && source venv/bin/activate && python main.py &
	cd frontend && bun dev

# Stop all services (if running in background)
stop:
	@echo "Stopping all services..."
	pkill -f "php artisan reverb:start" || true
	pkill -f "php artisan queue:work" || true
	pkill -f "php artisan serve" || true
	pkill -f "python main.py" || true
	pkill -f "bun dev" || true
	@echo "All services stopped"
