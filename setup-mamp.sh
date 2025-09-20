#!/bin/bash

# MAMP Integration Setup for PMP Theme Testing
# This script sets up the theme in MAMP for testing

MAMP_WP_PATH="/Applications/MAMP/htdocs/mi"
PMP_THEME_PATH="/Users/bonzysalesman/pmp-exam-prep/wp-content/themes/pmp-dashboard"
MAMP_THEMES_PATH="$MAMP_WP_PATH/wp-content/themes"

echo "🚀 Setting up PMP theme in MAMP..."

# Check if MAMP WordPress exists
if [ ! -d "$MAMP_WP_PATH" ]; then
    echo "❌ MAMP WordPress not found at $MAMP_WP_PATH"
    exit 1
fi

# Create symlink to our theme
if [ -L "$MAMP_THEMES_PATH/pmp-dashboard" ]; then
    echo "🔗 Removing existing symlink..."
    rm "$MAMP_THEMES_PATH/pmp-dashboard"
fi

echo "🔗 Creating symlink to PMP theme..."
ln -s "$PMP_THEME_PATH" "$MAMP_THEMES_PATH/pmp-dashboard"

# Copy test files
echo "📋 Copying test files..."
cp /Users/bonzysalesman/pmp-exam-prep/test-data.sql "$MAMP_WP_PATH/"
cp /Users/bonzysalesman/pmp-exam-prep/test-theme.php "$MAMP_WP_PATH/"

# Check wp-config.php
if [ -f "$MAMP_WP_PATH/wp-config.php" ]; then
    echo "✅ WordPress configuration found"
else
    echo "⚠️  WordPress not configured yet"
fi

echo ""
echo "✅ Setup complete!"
echo ""
echo "📋 Next steps:"
echo "1. Start MAMP"
echo "2. Visit: http://localhost:8888/mi/wp-admin/"
echo "3. Go to Appearance > Themes"
echo "4. Activate 'PMP Dashboard Theme'"
echo "5. Import test data: http://localhost:8888/mi/test-data.sql"
echo "6. Test theme: http://localhost:8888/mi/test-theme.php"
echo ""
echo "🌐 URLs to test:"
echo "   Homepage: http://localhost:8888/mi/"
echo "   Admin: http://localhost:8888/mi/wp-admin/"
echo "   Test Script: http://localhost:8888/mi/test-theme.php"
