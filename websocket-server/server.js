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

// Disconnect specific WebSocket connection
app.post('/disconnect', (req, res) => {
    const { websocket_id } = req.body;
    
    if (!websocket_id) {
        return res.status(400).json({ error: 'websocket_id required' });
    }
    
    const socket = io.sockets.sockets.get(websocket_id);
    if (socket) {
        console.log(`[WebSocket] Force disconnecting ${websocket_id}`);
        socket.disconnect(true);
        res.json({ success: true, message: 'Connection disconnected' });
    } else {
        res.status(404).json({ error: 'WebSocket connection not found' });
    }
});

// Invalidate specific session - triggers immediate validation
app.post('/invalidate-session', async (req, res) => {
    const { user_id, session_id } = req.body;
    
    if (!user_id || !session_id) {
        return res.status(400).json({ error: 'user_id and session_id required' });
    }
    
    console.log(`[WebSocket] Session invalidation requested for user ${user_id}, session ${session_id}`);
    
    // Find all sockets for this user and check their session
    io.sockets.sockets.forEach((socket) => {
        if (socket.userId == user_id && socket.sessionId === session_id) {
            console.log(`[WebSocket] Immediately disconnecting socket ${socket.id} for invalidated session ${session_id}`);
            socket.emit('session_invalidated', { 
                message: 'Your session has been terminated from another location.' 
            });
            setTimeout(() => socket.disconnect(true), 1000);
        }
    });
    
    res.json({ success: true, message: 'Session invalidation processed' });
});

// Invalidate all sessions except current - triggers immediate validation
app.post('/invalidate-all-sessions', async (req, res) => {
    const { user_id, except_session_id } = req.body;
    
    if (!user_id) {
        return res.status(400).json({ error: 'user_id required' });
    }
    
    console.log(`[WebSocket] All sessions invalidation requested for user ${user_id}, except ${except_session_id}`);
    
    // Find all sockets for this user and disconnect except the current one
    io.sockets.sockets.forEach((socket) => {
        if (socket.userId == user_id && socket.sessionId !== except_session_id) {
            console.log(`[WebSocket] Immediately disconnecting socket ${socket.id} for user ${user_id}`);
            socket.emit('session_invalidated', { 
                message: 'You have been logged out from all other devices.' 
            });
            setTimeout(() => socket.disconnect(true), 1000);
        }
    });
    
    res.json({ success: true, message: 'All sessions invalidation processed' });
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

/**
 * Extract session ID from cookie string
 * @param {string} cookieString - Cookie header string
 * @returns {string|null} Session ID or null
 */
function extractSessionFromCookie(cookieString) {
    if (!cookieString) return null;
    
    // Try to extract welinked_session cookie
    const match = cookieString.match(/welinked_session=([^;]+)/);
    if (match) {
        console.log(`[WebSocket] Extracted session ID from welinked_session cookie: ${match[1]}`);
        return match[1];
    }
    
    // Fallback to CAKEPHP cookie if welinked_session not found
    const cakeMatch = cookieString.match(/CAKEPHP=([^;]+)/);
    if (cakeMatch) {
        console.log(`[WebSocket] Extracted session ID from CAKEPHP cookie: ${cakeMatch[1]}`);
        return cakeMatch[1];
    }
    
    console.log(`[WebSocket] No session cookie found in: ${cookieString}`);
    return null;
}

// Socket.io connection handling
io.on('connection', async (socket) => {
    console.log(`[WebSocket] New connection: ${socket.id}`);
    
    // Authenticate user
    const token = socket.handshake.auth.token;
    const sessionId = socket.handshake.auth.sessionId || extractSessionFromCookie(socket.handshake.headers.cookie);
    
    if (!token) {
        console.log(`[WebSocket] No token provided, disconnecting ${socket.id}`);
        socket.disconnect();
        return;
    }

    console.log(`[WebSocket] Session ID extracted: ${sessionId}`);

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
        socket.sessionId = sessionId;
        userConnections.set(userId, socket.id);
        
        // Update session record with WebSocket ID if sessionId provided
        if (sessionId) {
            try {
                await updateSessionWebSocketId(sessionId, socket.id, userId);
                console.log(`[WebSocket] Updated session ${sessionId} with WebSocket ID ${socket.id}`);
            } catch (error) {
                console.error(`[WebSocket] Failed to update session:`, error.message);
            }
        }
        
        // Join user's personal room
        socket.join(`user:${userId}`);
        
        console.log(`[WebSocket] ✓ User ${userId} authenticated and connected: ${socket.id}`);
        
        // Send initial connection success
        socket.emit('authenticated', {
            userId: userId,
            sessionId: sessionId,
            timestamp: new Date().toISOString()
        });

        // Handle disconnection
        socket.on('disconnect', (reason) => {
            console.log(`[WebSocket] User ${userId} disconnected: ${reason}`);
            userConnections.delete(userId);
            
            // Clear WebSocket ID from session record
            if (sessionId) {
                clearSessionWebSocketId(sessionId, userId)
                    .catch(error => console.error('Failed to clear WebSocket ID:', error.message));
            }
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

/**
 * Update session record with WebSocket ID
 * @param {string} sessionId - Session ID
 * @param {string} websocketId - WebSocket connection ID  
 * @param {number} userId - User ID
 */
async function updateSessionWebSocketId(sessionId, websocketId, userId) {
    const mysql = require('mysql2/promise');
    
    const connection = await mysql.createConnection({
        host: process.env.DB_HOST || 'localhost',
        user: process.env.DB_USER || 'root',
        password: process.env.DB_PASSWORD || 'welinked@password',
        database: process.env.DB_NAME || 'welinked_db'
    });

    try {
        await connection.execute(
            'UPDATE user_sessions SET websocket_id = ? WHERE session_id = ? AND user_id = ?',
            [websocketId, sessionId, userId]
        );
    } finally {
        await connection.end();
    }
}

/**
 * Clear WebSocket ID from session record
 * @param {string} sessionId - Session ID
 * @param {number} userId - User ID  
 */
async function clearSessionWebSocketId(sessionId, userId) {
    const mysql = require('mysql2/promise');
    
    const connection = await mysql.createConnection({
        host: process.env.DB_HOST || 'localhost',
        user: process.env.DB_USER || 'root', 
        password: process.env.DB_PASSWORD || 'welinked@password',
        database: process.env.DB_NAME || 'welinked_db'
    });

    try {
        await connection.execute(
            'UPDATE user_sessions SET websocket_id = NULL WHERE session_id = ? AND user_id = ?',
            [sessionId, userId]
        );
    } finally {
        await connection.end();
    }
}

/**
 * Validate if a session is still active in the database
 * @param {string} sessionId - Session ID
 * @param {number} userId - User ID  
 */
async function validateSession(sessionId, userId) {
    if (!sessionId || !userId) {
        return false;
    }

    const mysql = require('mysql2/promise');
    
    const connection = await mysql.createConnection({
        host: process.env.DB_HOST || 'localhost',
        user: process.env.DB_USER || 'root', 
        password: process.env.DB_PASSWORD || 'welinked@password',
        database: process.env.DB_NAME || 'welinked_db'
    });

    try {
        // Check both user_sessions AND sessions table
        // Session must exist in both tables to be considered valid
        const [userSessionRows] = await connection.execute(
            'SELECT id FROM user_sessions WHERE session_id = ? AND user_id = ? LIMIT 1',
            [sessionId, userId]
        );
        
        const [phpSessionRows] = await connection.execute(
            'SELECT id FROM sessions WHERE id = ? LIMIT 1',
            [sessionId]
        );
        
        const isValid = userSessionRows.length > 0 && phpSessionRows.length > 0;
        
        if (!isValid) {
            console.log(`[WebSocket] Session validation failed for ${sessionId}: user_sessions=${userSessionRows.length}, sessions=${phpSessionRows.length}`);
        }
        
        return isValid;
    } catch (error) {
        console.error('Session validation query failed:', error);
        return false;
    } finally {
        await connection.end();
    }
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
