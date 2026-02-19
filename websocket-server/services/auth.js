/**
 * Auth Service
 * Verifies user tokens with PHP backend
 */

const axios = require('axios');

class AuthService {
    constructor() {
        this.phpApiUrl = process.env.PHP_API_URL || 'http://php:80';
    }

    /**
     * Verify authentication token with PHP backend
     * @param {string} token - JWT or session token
     * @returns {number|null} - User ID if valid, null if invalid
     */
    async verifyToken(token) {
        try {
            const url = `${this.phpApiUrl}/api/auth/verify-token`;
            console.log(`[Auth] Verifying WebSocket token at: ${url}`);
            console.log(`[Auth] Token: ${token?.substring(0, 30)}...`);
            
            // Send token in request body
            const response = await axios.post(
                url,
                { token },
                {
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    timeout: 5000
                }
            );

            console.log(`[Auth] Response status: ${response.status}`);
            console.log(`[Auth] Response data:`, JSON.stringify(response.data).substring(0, 200));
            console.log(`[Auth] response.data.success:`, response.data?.success);
            console.log(`[Auth] response.data.userId:`, response.data?.userId);

            if (response.data && response.data.success && response.data.userId) {
                console.log(`[Auth] ✓ Token verified for user ${response.data.userId} (${response.data.username})`);
                return response.data.userId;
            }

            console.log('[Auth] ✗ Token verification failed - invalid response structure');
            return null;
        } catch (error) {
            if (error.response?.status === 401 || error.response?.status === 400) {
                console.log(`[Auth] ✗ Unauthorized token (${error.response.status})`);
                console.log('[Auth] Response:', error.response?.data?.message);
            } else if (error.code === 'ECONNREFUSED') {
                console.error(`[Auth] ✗ Connection refused to ${this.phpApiUrl}`);
            } else {
                console.error('[Auth] ✗ Token verification error:', error.message);
                console.error('[Auth] Error details:', error.code, error.response?.status);
            }
            return null;
        }
    }

    /**
     * Get user info from PHP backend
     * @param {number} userId - User ID
     * @returns {object|null} - User data if found
     */
    async getUserInfo(userId) {
        try {
            const response = await axios.get(
                `${this.phpApiUrl}/api/users/${userId}`,
                { timeout: 5000 }
            );

            return response.data;
        } catch (error) {
            console.error('[Auth] Get user info error:', error.message);
            return null;
        }
    }
}

module.exports = AuthService;
