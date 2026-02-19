/**
 * WeLinked WebSocket Server
 * Handles real-time notifications via Socket.io
 */

require('dotenv').config();
const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const cors = require('cors');
const RedisService = require('./services/redis');
const AuthService = require('./services/auth');

const app = express();
const server = http.createServer(app);

// CORS configuration
const io = new Server(server, {
    cors: {
        origin: process.env.CORS_ORIGIN || '*',
        methods: ['GET', 'POST'],
        credentials: true
    },
    pingTimeout: 60000,
    pingInterval: 25000,
    // Reduce logging verbosity
    serveClient: false,
    // Only log errors in production
    transports: ['websocket', 'polling']
});

app.use(cors());
app.use(express.json());

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({
        status: 'ok',
        service: 'websocket-server',
        connections: io.engine.clientsCount,
        uptime: process.uptime()
    });
});

// Connection stats endpoint
app.get('/stats', (req, res) => {
    res.json({
        connections: io.engine.clientsCount,
        rooms: io.sockets.adapter.rooms.size
    });
});

// Store user connections (userId -> socketId mapping)
const userConnections = new Map();

// Initialize Redis
const redis = new RedisService();
const authService = new AuthService();

// Socket.io connection handling
io.on('connection', async (socket) => {
    console.log(`[WebSocket] New connection: ${socket.id}`);
    
    // Authenticate user
    const token = socket.handshake.auth.token;
    if (!token) {
        console.log(`[WebSocket] No token provided, disconnecting ${socket.id}`);
        socket.disconnect();
        return;
    }

    try {
        // Verify token with PHP backend
        const userId = await authService.verifyToken(token);
        if (!userId) {
            console.log(`[WebSocket] Invalid token, disconnecting ${socket.id}`);
            socket.disconnect();
            return;
        }

        // Store user connection
        socket.userId = userId;
        userConnections.set(userId, socket.id);
        
        // Join user's personal room
        socket.join(`user:${userId}`);
        
        console.log(`[WebSocket] User ${userId} authenticated and connected: ${socket.id}`);
        
        // Send initial connection success
        socket.emit('authenticated', {
            userId: userId,
            timestamp: new Date().toISOString()
        });

        // Handle disconnection
        socket.on('disconnect', (reason) => {
            console.log(`[WebSocket] User ${userId} disconnected: ${reason}`);
            userConnections.delete(userId);
        });

        // Handle manual notification read
        socket.on('notification:read', (notificationId) => {
            console.log(`[WebSocket] User ${userId} read notification ${notificationId}`);
            // Optional: Broadcast to other user's devices
            socket.to(`user:${userId}`).emit('notification:read', notificationId);
        });

        // Handle typing indicators (future feature)
        socket.on('typing:start', (data) => {
            socket.to(`user:${data.recipientId}`).emit('typing:start', {
                userId: userId,
                context: data.context
            });
        });

        socket.on('typing:stop', (data) => {
            socket.to(`user:${data.recipientId}`).emit('typing:stop', {
                userId: userId
            });
        });

    } catch (error) {
        console.error(`[WebSocket] Authentication error:`, error);
        socket.disconnect();
    }
});

// Subscribe to Redis channels for notifications
async function subscribeToNotifications() {
    console.log('[Redis] Subscribing to notification channels...');
    
    await redis.subscribe('notifications:*', (channel, message) => {
        try {
            // Extract userId from channel name (e.g., "notifications:123")
            const userId = channel.split(':')[1];
            const data = JSON.parse(message);
            
            console.log(`[Redis] Broadcasting notification to user ${userId}:`, data.type);
            
            // Emit to all user's connected devices
            io.to(`user:${userId}`).emit('notification', data);
            
        } catch (error) {
            console.error('[Redis] Error processing notification:', error);
        }
    });

    console.log('[Redis] Subscribed to notification channels');
}

// Start server
const PORT = process.env.PORT || 3000;

server.listen(PORT, async () => {
    console.log(`[WebSocket Server] Running on port ${PORT}`);
    console.log(`[Environment] ${process.env.NODE_ENV || 'development'}`);
    
    try {
        await redis.connect();
        await subscribeToNotifications();
        console.log('[Server] All services initialized successfully');
    } catch (error) {
        console.error('[Server] Failed to initialize services:', error);
        process.exit(1);
    }
});

// Graceful shutdown
process.on('SIGTERM', async () => {
    console.log('[Server] SIGTERM received, shutting down gracefully...');
    
    io.close(() => {
        console.log('[Server] All connections closed');
    });
    
    await redis.disconnect();
    process.exit(0);
});

process.on('SIGINT', async () => {
    console.log('[Server] SIGINT received, shutting down gracefully...');
    
    io.close(() => {
        console.log('[Server] All connections closed');
    });
    
    await redis.disconnect();
    process.exit(0);
});

module.exports = { io, server };
