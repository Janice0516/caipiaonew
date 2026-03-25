#!/bin/bash
echo "Starting deployment of pure caipiaonew application..."

# 1. Frontend Build
echo "Building Vue3 User Frontend..."
if command -v npm &> /dev/null; then
    cd frontend
    npm install
    npm run build
    cd ..
else
    echo "Warning: npm could not be found, skipping frontend build."
fi

# 2. Set directory permissions
echo "Setting permissions for backend cache and upload directories..."
chmod -R 777 caches/
chmod -R 777 uppic/

echo "Deployment script execution finished!"
echo "--------------------------------------------------------"
echo "Next steps:"
echo "1. Import 'database.sql' into your MySQL server."
echo "2. Configure your database settings in 'configs/database.php'."
echo "3. Configure your web server (Nginx/Apache) root to this directory."
echo "--------------------------------------------------------"
