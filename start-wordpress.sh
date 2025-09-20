#!/bin/bash

echo "🚀 Starting PMP WordPress Development Environment..."

# Create wp-content directories if they don't exist
mkdir -p wp-content/themes
mkdir -p wp-content/plugins
mkdir -p wp-content/uploads

# Start Docker containers
docker-compose up -d

echo "⏳ Waiting for WordPress to be ready..."
sleep 30

echo "✅ WordPress is ready!"
echo ""
echo "🌐 Access your sites:"
echo "   WordPress: http://localhost:8080"
echo "   phpMyAdmin: http://localhost:8081"
echo ""
echo "📝 WordPress Setup:"
echo "   Database: wordpress"
echo "   Username: wordpress"
echo "   Password: wordpress"
echo ""
echo "🎨 Theme Location: wp-content/themes/pmp-dashboard/"
echo ""
echo "To stop: docker-compose down"
