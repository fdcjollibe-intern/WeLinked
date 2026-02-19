#!/bin/bash

echo "========================================"
echo "WeLinked WebSocket Setup Verification"
echo "========================================"
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if docker-compose is running
echo "1. Checking Docker services..."
if docker-compose ps | grep -q "Up"; then
    echo -e "${GREEN}✓${NC} Docker services are running"
else
    echo -e "${RED}✗${NC} Docker services are not running"
    echo "   Run: docker-compose up -d"
    exit 1
fi

# Check Redis
echo ""
echo "2. Checking Redis..."
if docker-compose exec -T redis redis-cli ping 2>/dev/null | grep -q "PONG"; then
    echo -e "${GREEN}✓${NC} Redis is responding"
else
    echo -e "${RED}✗${NC} Redis is not responding"
fi

# Check WebSocket server
echo ""
echo "3. Checking WebSocket server..."
if curl -s http://localhost:3000/health 2>/dev/null | grep -q "ok"; then
    echo -e "${GREEN}✓${NC} WebSocket server is running"
    echo ""
    echo "   Server stats:"
    curl -s http://localhost:3000/stats 2>/dev/null | python3 -m json.tool 2>/dev/null || echo "   (Stats available at http://localhost:3000/stats)"
else
    echo -e "${RED}✗${NC} WebSocket server is not responding"
    echo "   Check logs: docker-compose logs websocket"
fi

# Check PHP backend
echo ""
echo "4. Checking PHP backend..."
if curl -s http://localhost/ 2>/dev/null | grep -q "WeLinked"; then
    echo -e "${GREEN}✓${NC} PHP backend is responding"
else
    echo -e "${YELLOW}⚠${NC} PHP backend might not be accessible on http://localhost/"
fi

# Check if WebSocket client files exist
echo ""
echo "5. Checking client files..."
if [ -f "backend/webroot/js/websocket-client.js" ]; then
    echo -e "${GREEN}✓${NC} websocket-client.js exists"
else
    echo -e "${RED}✗${NC} websocket-client.js is missing"
fi

if grep -q "socket.io" backend/templates/Dashboard/index.php 2>/dev/null; then
    echo -e "${GREEN}✓${NC} Socket.io script tag found in Dashboard"
else
    echo -e "${YELLOW}⚠${NC} Socket.io script tag not found in Dashboard"
fi

# Final summary
echo ""
echo "========================================"
echo "Setup Status"
echo "========================================"
echo ""
echo "Next steps:"
echo "1. Open http://localhost in your browser"
echo "2. Open browser console (F12)"
echo "3. Look for: [WebSocket] Connected"
echo "4. Comment on a post to test real-time notifications"
echo ""
echo "Monitoring URLs:"
echo "  • Health: http://localhost:3000/health"
echo "  • Stats:  http://localhost:3000/stats"
echo ""
echo "Troubleshooting:"
echo "  • WebSocket logs: docker-compose logs -f websocket"
echo "  • Redis monitor:  docker-compose exec redis redis-cli monitor"
echo ""
