#!/bin/bash

# This script syncs the local WordPress theme to the MAMP testing environment.

# Source directory within the project
SOURCE_DIR="./wp-content/themes/pmp-dashboard/"

# Destination directory for the MAMP environment
DEST_DIR="/Applications/MAMP/htdocs/mi/wp-content/themes/pmp-dashboard/"

# Ensure the destination directory exists
mkdir -p "$DEST_DIR"

# Use rsync to copy files.
# -a: archive mode (preserves permissions, etc.)
# -v: verbose (shows what's being copied)
# --delete: deletes files from DEST_DIR that are not in SOURCE_DIR
echo "🔄 Syncing theme files to MAMP..."
rsync -av --delete "$SOURCE_DIR" "$DEST_DIR"
echo "✅ Sync complete."
