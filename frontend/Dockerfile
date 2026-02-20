## Multi-stage Dockerfile for Yuasa Battery Inspection FE
## Stage 1: Build React/TanStack Router app using Bun
FROM oven/bun:latest AS builder

WORKDIR /app

# Install dependencies (use bun.lock if present for reproducible builds)
COPY bun.lock* package.json ./

# Copy the rest of the app source
COPY . .

# Install dependencies and build for production
RUN bun install --frozen-lockfile \
  && bun --bun run build

## Stage 2: Serve built assets via Nginx with reverse proxy
FROM nginx:1.27-alpine AS runner

ENV BACKEND_URL=http://host.docker.internal:8080

WORKDIR /usr/share/nginx/html

# Remove default nginx static assets
RUN rm -rf ./*

# Copy built app from builder stage
COPY --from=builder /app/dist ./

# Copy custom nginx config template for envsubst
COPY nginx.conf /etc/nginx/templates/default.conf.template

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
