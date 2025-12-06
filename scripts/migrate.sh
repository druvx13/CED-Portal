#!/bin/bash

# Migration script for deployment

echo "Migrating uploads..."
if [ -d "uploads" ]; then
    mkdir -p public/uploads
    cp -r uploads/* public/uploads/
    echo "Uploads migrated."
else
    echo "No existing uploads directory found."
fi

echo "Setting permissions..."
chmod -R 755 public/uploads
echo "Done."
