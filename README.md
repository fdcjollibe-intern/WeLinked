# WeLinked - Social Media Platform

A modern social media platform with real-time notifications, posts, reels, reactions, comments, and friend connections. Built with CakePHP 5.3, Node.js WebSocket server, and Vue.js.

> **Quick Start**: `docker-compose up -d` → Open `http://localhost`

## Tech Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | PHP 8.2+ • CakePHP 5.3 • Composer  |
| **Frontend** | Vue.js 3 (CDN) • Socket.io Client |
| **Database** | MySQL 8.0 |
| **Real-Time** | Node.js 18+ • Socket.io • Redis 7 |
| **Infrastructure** | Docker Compose • Nginx • PHP-FPM |
| **CDN** | Cloudinary |

## Features

✅ User authentication (Argon2id)  
✅ Posts & Reels with media (250MB max)  
✅ 6 reaction types (like, love, haha, wow, sad, angry)  
✅ Comments with attachments  
✅ @mentions with autocomplete  
✅ Follow/unfollow system  
✅ Real-time WebSocket notifications  
✅ Birthday messages  
✅ Profile management  
✅ Multi-device sessions  
✅ Search & suggestions  

## Prerequisites

- Docker  Desktop 20.10+
- Git

## Installation

### Quick Start (Docker)

```bash
git clone <your-repo-url>
cd WeLinked
docker-compose up -d --build
```

Access at: **http://localhost**

### Configuration

1. **Backend**: Copy `backend/config/app_local_sample.php` to `app_local.php` and update:
   - `SECURITY_SALT` - Generate with: `php -r "echo bin2hex(random_bytes(32));"`
   - Cloudinary credentials (get from [cloudinary.com](https://cloudinary.com))

2. **Database**: Auto-initialized on first run from `db/init-db.sql`

3. **Services Running**:
   - Nginx: http://localhost
   - MySQL: localhost:3306
   - Redis: localhost:6379
   - WebSocket: localhost:3000

### Manual Setup (Development)

```bash
# Backend
cd backend && composer install

# WebSocket
cd websocket-server && npm install

# Database
mysql -u root -p < db/init-db.sql
```

## Essential Commands

```bash
# Start
docker-compose up -d

# Stop
docker-compose down

# View logs
docker-compose logs -f

# Rebuild
docker-compose up -d --build

# Run tests
cd backend && composer test

# Access containers
docker-compose exec backend sh
docker-compose exec websocket sh
```

## Project Structure

```
WeLinked/
├── backend/               # CakePHP 5.3 app
│   ├── config/           # App config, routes
│   ├── src/
│   │   ├── Controller/   # 25 controllers
│   │   ├── Model/        # Entities, Tables
│   │   └── Service/      # Business logic
│   ├── templates/        # Views (CTP)
│   ├── webroot/          # Public assets
│   └── composer.json
├── websocket-server/     # Node.js real-time server
│   ├── server.js
│   ├── services/         # Redis, Auth
│   └── package.json
├── db/                   # SQL schemas, migrations
├── nginx/                # Web server config
├── php/                  # PHP-FPM Dockerfile
└── Docker-compose.yaml   # 5 services (nginx, backend, db, redis, websocket)
```

## Key API Endpoints

**Authentication**
- `POST /login` - Login
- `POST /register` - Register
- `GET /logout` - Logout

**Posts**
- `GET /dashboard/middle-column` - Get feed
- `POST /dashboard/posts/create` - Create post
- `POST /dashboard/posts/edit/{id}` - Edit post
- `DELETE /dashboard/posts/delete/{id}` - Delete post

**Social**
- `POST /dashboard/reactions/react` - React to post/comment
- `POST /dashboard/comments/create` - Add comment
- `POST /friends/follow` - Follow user
- `POST /friends/unfollow` - Unfollow user

**Notifications**
- `GET /api/notifications` - Get notifications
- `GET /api/notifications/unread-count` - Unread count
- `POST /api/notifications/mark-read/{id}` - Mark as read

**Profile**
- `GET /profile/{username}` - View profile
- `POST /profile/update` - Update profile
- `GET /profile/{username}/followers` - Get followers

**Real-Time**
- WebSocket: `ws://localhost:3000` (auto-connects)
- Events: `notification`, `session_invalidated`

> **Full API docs**: See [backend/config/routes.php](backend/config/routes.php)

## Database

**Core Tables**: users, posts, comments, reactions, friendships, notifications, mentions, birthday_messages, user_sessions, post_attachments, comment_attachments

**Key Features**:
- Argon2id password hashing
- Soft deletes with `deleted_at`
- Foreign keys with CASCADE
- Indexed for performance
- UTF8MB4 support

## Real-Time Notifications

**Architecture**: Socket.io + Redis Pub/Sub

```
User Action → CakePHP → Redis Pub/Sub → WebSocket Server → Client
```

**Client Events**: `notification`, `session_invalidated`, `authenticated`  
**Fallback**: Auto-polling every 30s if WebSocket fails  
**Health Check**: `GET http://localhost:3000/health`

## Security

- **Passwords**: Argon2id hashing with auto-rehashing
- **Sessions**: HttpOnly, SameSite=Lax, 120min timeout
- **CSRF**: SameSite cookies, FormProtection available
- **SQL Injection**: CakePHP ORM (parameterized queries)
- **XSS**: Auto-escaping in templates, HttpOnly cookies
- **Files**: Type validation, size limits (250MB), CDN storage
- **Secrets**: `app_local.php` gitignored

**Generate Salt**: `php -r "echo bin2hex(random_bytes(32));"`

## Contributing

```bash
# Create branch
git checkout -b feature/your-feature

# Make changes, then run checks
cd backend
composer cs-check       # Code standards
composer test           # Tests

# Commit (use conventional commits)
git commit -m "feat: add feature"
git commit -m "fix: resolve bug"
git commit -m "docs: update readme"

# Push and create PR
git push origin feature/your-feature
```

## License

MIT License. See project for full text.

---

**Documentation**: See `BIRTHDAY_FEATURE_COMPLETE.md`, `WEBSOCKET_SETUP_COMPLETE.md`, `CLOUDINARY_SETUP.md` for feature-specific guides.

**Resources**: [CakePHP 5](https://book.cakephp.org/5/) • [Socket.io](https://socket.io/docs/) • [Docker](https://docs.docker.com/) • [Vue.js 3](https://vuejs.org/)
