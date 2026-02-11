#!/bin/bash
# Watch login logs in real-time

echo "Watching login attempts... (Press Ctrl+C to stop)"
echo "================================================"
echo ""

docker exec welinked-backend tail -f /var/www/html/logs/error.log | grep --line-buffered -E "(LOGIN|Authentication|Username|Password|Request Data|Response status)" --color=always
