# WeLinked - Social Media Platform

A full-featured social media web application built with CakePHP 5, MySQL 8, and real-time WebSocket notifications.

## Tech Stack

| Layer | Technology |
|-------|------------|
| **Backend** | CakePHP 5.3.0, PHP 8.2 |
| **Frontend** | Vanilla JavaScript (ES6+), HTML5, CSS3 |
| **Database** | MySQL 8.0 |
| **Caching/Pub-Sub** | Redis 7 |
| **Real-time** | Node.js WebSocket Server |
| **Web Server** | Nginx |
| **Dev Runtime** | Docker, Docker Compose |

### Key Dependencies

- **CakePHP Plugins**: `cakephp/authentication` ^4.0, `cakephp/migrations` ^4.0
- **Cloud Storage**: `cloudinary/cloudinary_php` ^3.1
- **Device Detection**: `mobiledetect/mobiledetectlib` ^4.8

---

## Project Structure

```
WeLinked-main/
├── backend/                    # CakePHP 5 application
│   ├── config/                  # Configuration files
│   │   ├── app.php             # Application config
│   │   ├── routes.php          # Routing definitions
│   │   └── bootstrap.php       # Bootstrap file
│   ├── src/
│   │   ├── Controller/         # CakePHP controllers
│   │   ├── Model/              # ORM tables, entities, behaviors
│   │   │   ├── Table/          # Table classes
│   │   │   └── Entity/         # Entity classes
│   │   ├── Service/            # Business logic services
│   │   └── View/               # Views, cells, helpers
│   ├── templates/              # Template files (CTP)
│   │   ├── layout/             # Layout templates
│   │   ├── element/            # Reusable elements
│   │   └── [Controller]/       # Controller-specific views
│   └── webroot/                # Web root, assets, JS
├── db/                         # Database schemas & seeds
│   ├── init-db.sql             # Main schema
│   └── withdata/               # Pre-populated data
├── websocket-server/           # Node.js WebSocket server
├── php/                        # PHP-FPM Dockerfile
├── nginx/                      # Nginx configuration
└── docker-compose.yaml         # Docker Compose setup
```

---

## Getting Started

### Prerequisites

- Docker & Docker Compose
- Ports 80, 3306, 6379, 3000 available

### Quick Start

```bash
# Clone and navigate to project
cd WeLinked-main

# Start all services
docker-compose up --build

# Wait for containers to start (30-60 seconds)
# Access the application
open http://localhost
```

### Default Credentials

The database seeds a test user on first run:

| Field | Value |
|-------|-------|
| Username | `johndoe` |
| Email | `johndoe@example.com` |
| Password | `password123` |

### Environment Variables

The application uses these environment variables (configured in `docker-compose.yaml`):

```yaml
DB_HOST: db
DB_PORT: 3306
DB_NAME: welinked_db
DB_USER: welinked
DB_PASSWORD: welinked@password
DEBUG: 1
REDIS_HOST: redis
REDIS_PORT: 6379
```

---

## Docker Services

| Service | Port | Description |
|---------|------|-------------|
| `nginx` | 80, 443 | Reverse proxy & web server |
| `backend` | 9000 (PHP-FPM) | CakePHP application |
| `db` | 3306 | MySQL 8.0 database |
| `redis` | 6379 | Redis for sessions & pub/sub |
| `websocket` | 3000 | Node.js WebSocket server |

### Service URLs (from within containers)

- **Backend**: `http://backend`
- **Database**: `db:3306`
- **Redis**: `redis:6379`
- **WebSocket**: `ws://websocket:3000`

---

## Architecture Overview

### Request Flow

```
Client Browser
      │
      ▼ (HTTP/HTTPS)
Nginx (Port 80)
      │
      ▼ (PHP-FPM)
CakePHP Application
      │
      ├─► MySQL (queries)
      ├─► Redis (sessions/cache)
      └─► WebSocket (real-time events)
```

### Frontend Architecture

This is a **server-side rendered (SSR)** application with vanilla JavaScript for interactivity. Unlike Vue.js single-page applications, each page is rendered by CakePHP and enhanced with JavaScript components.

**JavaScript Components** (in `backend/webroot/js/`):

| File | Purpose |
|------|---------|
| `dashboard.js` | Dashboard component loading, infinite scroll |
| `notifications.js` | Notification polling & display |
| `websocket-client.js` | WebSocket connection management |
| `comments.js` | Comment creation, editing, deletion |
| `reactions.js` | Like/emoji reaction handling |
| `post-composer.js` | Post creation with media & mentions |
| `search.js` | Search autocomplete |
| `reels.js` | Video reel viewing |
| `mentions.js` | @mention autocomplete |

---

## Feature Breakdown

### Landing & Authentication

| Feature | Route | Controller::Action | Auth Required |
|---------|-------|-------------------|---------------|
| Landing Page | `/` | `LoginController::index` | No |
| Login | `/login` | `LoginController::index` | No |
| Register | `/register` | `RegisterController::index` | No |
| Forgot Password | `/forgot-password` | `PasswordsController::forgot` | No |
| Logout | `/logout` | `LoginController::logout` | Yes |

### Dashboard (Main Feed)

| Feature | Route | Controller::Action | Auth Required |
|---------|-------|-------------------|---------------|
| Dashboard Home | `/dashboard` | `DashboardController::index` | Yes |
| Left Sidebar | `/dashboard/left-sidebar` | `DashboardLeftSidebarController::index` | Yes |
| Middle Column (Feed) | `/dashboard/middle-column` | `DashboardMiddleColumnController::index` | Yes |
| Right Sidebar | `/dashboard/right-sidebar` | `DashboardRightSidebarController::index` | Yes |

### Posts & Interactions

| Feature | Route | Controller::Action | Auth Required |
|---------|-------|-------------------|---------------|
| Create Post | `/dashboard/posts/create` | `DashboardPostsController::create` | Yes |
| Edit Post | `/dashboard/posts/edit/{id}` | `DashboardPostsController::edit` | Yes |
| Delete Post | `/dashboard/posts/delete/{id}` | `DashboardPostsController::delete` | Yes |
| View Single Post | `/post/{id}` | `DashboardPostsController::view` | Yes |
| Add Comment | `/dashboard/comments/create` | `DashboardCommentsController::create` | Yes |
| List Comments | `/dashboard/comments/list` | `DashboardCommentsController::list` | Yes |
| Edit Comment | `/dashboard/comments/edit` | `DashboardCommentsController::edit` | Yes |
| Delete Comment | `/dashboard/comments/delete` | `DashboardCommentsController::delete` | Yes |
| React (Like/Emoji) | `/dashboard/posts/react` | `DashboardReactionsController::react` | Yes |
| Upload Media | `/dashboard/upload` | `DashboardUploadsController::upload` | Yes |

### Friends & Social

| Feature | Route | Controller::Action | Auth Required |
|---------|-------|-------------------|---------------|
| Friends List | `/friends` | `FriendsController::index` | Yes |
| Follow User | `/friends/follow` | `FriendsController::follow` | Yes |
| Unfollow User | `/friends/unfollow` | `FriendsController::unfollow` | Yes |
| Friend Suggestions | `/api/friends/suggestions` | `FriendsController::suggestions` | Yes |
| Friends Count | `/api/friends/count` | `FriendsController::count` | Yes |

### Notifications & Real-time

| Feature | Route | Controller::Action | Auth Required |
|---------|-------|-------------------|---------------|
| Notifications List | `/api/notifications` | `NotificationsController::index` | Yes |
| Unread Count | `/api/notifications/unread-count` | `NotificationsController::unreadCount` | Yes |
| Mark as Read | `/api/notifications/mark-read/{id}` | `NotificationsController::markAsRead` | Yes |
| Mark All Read | `/api/notifications/mark-all-read` | `NotificationsController::markAllAsRead` | Yes |
| WebSocket Token | `/api/auth/websocket-token` | `AuthApiController::generateWebSocketToken` | Yes |

### Birthdays

| Feature | Route | Controller::Action | Auth Required |
|---------|-------|-------------------|---------------|
| Birthday Dashboard | `/birthdays` | `BirthdaysController::index` | Yes |
| Birthday List | `/birthday` | `BirthdaysController::list` | Yes |
| Send Birthday Message | `/birthday/send-message` | `BirthdaysController::sendMessage` | Yes |
| Birthday Messages | `/birthday/messages` | `BirthdaysController::messages` | Yes |
| Sent Birthday Wishes | `/birthday/sent` | `BirthdaysController::sent` | Yes |

### Profile & Settings

| Feature | Route | Controller::Action | Auth Required |
|---------|-------|-------------------|---------------|
| View Profile | `/profile/{username}` | `ProfileController::index` | No |
| Own Profile | `/profile` | `ProfileController::index` | Yes |
| Followers | `/profile/{username}/followers` | `ProfileController::followers` | No |
| Following | `/profile/{username}/following` | `ProfileController::following` | No |
| Update Profile | `/profile/update` | `ProfileController::update` | Yes |
| Settings | `/settings` | `SettingsController::index` | Yes |
| Update Account | `/settings/update-account` | `SettingsController::updateAccount` | Yes |
| Update Password | `/settings/update-password` | `SettingsController::updatePassword` | Yes |
| Update Theme | `/settings/update-theme` | `SettingsController::updateTheme` | Yes |
| Upload Profile Photo | `/settings/upload-profile-photo` | `SettingsController::uploadProfilePhoto` | Yes |

### Reels (Video Posts)

| Feature | Route | Controller::Action | Auth Required |
|---------|-------|-------------------|---------------|
| Reels Feed | `/reels` | `ReelsController::index` | Yes |

### Search & Discovery

| Feature | Route | Controller::Action | Auth Required |
|---------|-------|-------------------|---------------|
| Search | `/search` | `SearchController::index` | Yes |
| Search Suggestions | `/api/search/suggest` | `SearchController::suggest` | Yes |
| Mentions Autocomplete | `/api/mentions/search` | `MentionsController::search` | Yes |

### Device & Security

| Feature | Route | Controller::Action | Auth Required |
|---------|-------|-------------------|---------------|
| Device Sessions | `/api/device-sessions` | `DeviceSessionsController::index` | Yes |
| Logout Device | `/api/device-sessions/logout` | `DeviceSessionsController::logoutDevice` | Yes |
| Logout All Devices | `/api/device-sessions/logout-all` | `DeviceSessionsController::logoutAllDevices` | Yes |

---

## Database Schema

### Core Tables

#### `users`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT |
| `full_name` | VARCHAR(150) | NOT NULL |
| `username` | VARCHAR(50) | NOT NULL, UNIQUE |
| `email` | VARCHAR(100) | NOT NULL, UNIQUE |
| `password_hash` | VARCHAR(255) | NOT NULL |
| `profile_photo_path` | VARCHAR(255) | NULL |
| `gender` | ENUM | 'Male', 'Female', 'Prefer not to say' |
| `theme_preference` | ENUM | 'system', 'light', 'dark' |
| `created_at` | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP |
| `updated_at` | DATETIME | NULL, ON UPDATE CURRENT_TIMESTAMP |

**Indexes**: `idx_users_username`

#### `posts`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT |
| `user_id` | BIGINT UNSIGNED | FOREIGN KEY → users(id), CASCADE |
| `content_text` | TEXT | NULL |
| `content_image_path` | VARCHAR(255) | NULL |
| `location` | VARCHAR(255) | NULL |
| `is_reel` | BOOLEAN | NULL (true for video posts) |
| `created_at` | DATETIME | NOT NULL |
| `updated_at` | DATETIME | NULL |
| `deleted_at` | DATETIME | NULL (soft delete) |

**Indexes**: `idx_posts_user_id`, `idx_posts_created_at`, `idx_posts_deleted_at`, `idx_posts_is_reel`

#### `comments`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY |
| `post_id` | BIGINT UNSIGNED | FOREIGN KEY → posts(id), CASCADE |
| `user_id` | BIGINT UNSIGNED | FOREIGN KEY → users(id), CASCADE |
| `content_text` | TEXT | NULL |
| `content_image_path` | VARCHAR(255) | NULL |
| `created_at` | DATETIME | NOT NULL |
| `updated_at` | DATETIME | NULL |
| `deleted_at` | DATETIME | NULL (soft delete) |

#### `reactions`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY |
| `user_id` | BIGINT UNSIGNED | FOREIGN KEY → users(id), CASCADE |
| `target_type` | ENUM | 'post', 'comment' |
| `target_id` | BIGINT UNSIGNED | NOT NULL |
| `reaction_type` | ENUM | 'like', 'haha', 'love', 'wow', 'sad', 'angry' |
| `created_at` | DATETIME | NOT NULL |
| `updated_at` | DATETIME | NULL |

**Unique Key**: `user_id, target_type, target_id` (one reaction per user per target)

#### `friendships`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY |
| `follower_id` | BIGINT UNSIGNED | FOREIGN KEY → users(id), CASCADE |
| `following_id` | BIGINT UNSIGNED | FOREIGN KEY → users(id), CASCADE |
| `created_at` | DATETIME | NOT NULL |

**Unique Key**: `follower_id, following_id`

#### `notifications`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY |
| `user_id` | BIGINT UNSIGNED | FOREIGN KEY → users(id), CASCADE |
| `actor_id` | BIGINT UNSIGNED | FOREIGN KEY → users(id), CASCADE |
| `type` | ENUM | 'mention', 'reaction', 'comment', 'follow' |
| `target_type` | ENUM | 'post', 'comment', 'user' |
| `target_id` | BIGINT UNSIGNED | NULL |
| `message` | TEXT | NOT NULL |
| `is_read` | BOOLEAN | NOT NULL, DEFAULT FALSE |
| `created_at` | DATETIME | NOT NULL |

#### `mentions`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY |
| `post_id` | BIGINT UNSIGNED | FOREIGN KEY → posts(id), CASCADE |
| `mentioned_user_id` | BIGINT UNSIGNED | FOREIGN KEY → users(id), CASCADE |
| `mentioned_by_user_id` | BIGINT UNSIGNED | FOREIGN KEY → users(id), CASCADE |
| `created_at` | DATETIME | NOT NULL |

#### `post_attachments`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY |
| `post_id` | BIGINT UNSIGNED | FOREIGN KEY → posts(id), CASCADE |
| `file_path` | VARCHAR(255) | NOT NULL |
| `file_type` | ENUM | 'image', 'video' |
| `file_size` | BIGINT UNSIGNED | NOT NULL |
| `display_order` | TINYINT UNSIGNED | NOT NULL, DEFAULT 0 |
| `upload_status` | ENUM | 'uploading', 'completed', 'failed' |
| `created_at` | DATETIME | NOT NULL |

#### `user_sessions`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY |
| `user_id` | BIGINT UNSIGNED | FOREIGN KEY → users(id), CASCADE |
| `session_id` | VARCHAR(255) | NOT NULL |
| `device_type` | VARCHAR(50) | NULL |
| `device_name` | VARCHAR(255) | NULL |
| `browser_name` | VARCHAR(100) | NULL |
| `ip_address` | VARCHAR(45) | NULL |
| `created_at` | DATETIME | NOT NULL |

### ORM Associations

#### PostsTable

```php
$this->belongsTo('Users', ['foreignKey' => 'user_id', 'joinType' => 'INNER']);
$this->hasMany('Reactions', [
    'foreignKey' => 'target_id',
    'conditions' => ['Reactions.target_type' => 'post'],
    'dependent' => true,
]);
$this->hasMany('Mentions', ['foreignKey' => 'post_id', 'dependent' => true]);
$this->hasMany('Comments', ['foreignKey' => 'post_id', 'dependent' => true]);
$this->hasMany('PostAttachments', ['foreignKey' => 'post_id', 'dependent' => true]);
```

#### UsersTable

```php
// Timestamp behavior for created_at, updated_at
$this->addBehavior('Timestamp', [...]);
```

#### FriendshipsTable

```php
$this->belongsTo('Followers', [
    'className' => 'Users',
    'foreignKey' => 'follower_id',
    'joinType' => 'INNER'
]);
$this->belongsTo('Following', [
    'className' => 'Users',
    'foreignKey' => 'following_id',
    'joinType' => 'INNER'
]);
```

---

## CakePHP Concepts Used

### Authentication & Authorization

The application uses **both CSRF protection and session-based authentication**:

#### CSRF Protection (CakePHP CsrfProtectionMiddleware)

```php
// src/Application.php
$csrf = new CsrfProtectionMiddleware([
    'httponly' => true,  // Prevents JavaScript access to CSRF cookie
]);

// Skip CSRF for JSON API requests
$csrf->skipCheckCallback(static function ($request) {
    if ($request->is('json')) {
        return true;
    }
});

$middlewareQueue->add($csrf);
```

**How CSRF Works**:
- CSRF token generated per request and exposed via `window.csrfToken`
- Token passed in `X-CSRF-Token` header for AJAX requests
- **Skipped for JSON API endpoints** (`$request->is('json')`)
- `httponly: true` means the cookie cannot be accessed by JavaScript (prevents XSS attacks from stealing the token)

#### Session-Based Authentication

```php
// src/Application.php
$authenticationService->loadAuthenticator('Authentication.Session');
$authenticationService->loadAuthenticator('Authentication.Form', [...]);
```

**Auth Flow**:
1. Login form submits to `LoginController::index`
2. Authentication component validates credentials
3. Password hashed with Argon2id (`DefaultPasswordHasher`)
4. Session cookie (`welinked_session`) set in browser
5. Session stored in database (`sessions` table) - **NOT file-based**
6. Device info captured using `WhichBrowser` parser
7. Device sessions tracked in `user_sessions` table

**Session Configuration** (`config/app.php`):
```php
'Session' => [
    'defaults' => 'php',
    'cookie' => 'welinked_session',
    'timeout' => 120, // 2 hours
    'cookieLifetime' => 2629800, // ~30 days (remember me)
]
```

#### Authenticated Request Headers

```http
Cookie: welinked_session=...
X-Requested-With: XMLHttpRequest
X-CSRF-Token: <csrf-token>  (for non-JSON endpoints)
```

### Validation

Validation rules defined in Table classes:

```php
// UsersTable.php
$validator
    ->scalar('username')
    ->maxLength('username', 50)
    ->requirePresence('username', 'create')
    ->notEmptyString('username')
    ->add('username', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

$validator
    ->email('email')
    ->maxLength('email', 100)
    ->requirePresence('email', 'create')
    ->notEmptyString('email');
```

### ORM & Query Building

Complex queries using Cake's Query Builder:

```php
// DashboardMiddleColumnController.php
$query = $postsTable->find()
    ->contain([
        'Users' => function (Query $q) { ... },
        'Reactions' => function (Query $q) { ... },
        'Mentions' => function (Query $q) { ... },
        'PostAttachments' => function (Query $q) { ... },
    ])
    ->where(['Posts.deleted_at IS' => null])
    ->orderBy(['Posts.created_at' => 'DESC'])
    ->limit($limit)
    ->offset($start);
```

### Behaviors

- **Timestamp**: Automatically manages `created_at` and `updated_at` fields

### Flash Messages

```php
$this->Flash->error(__('Invalid username or password'));
```

### JSON API Responses

Many endpoints return JSON for AJAX calls:

```php
// DashboardReactionsController.php
return $this->response
    ->withType('application/json')
    ->withStringBody(json_encode($resp));
```

### Soft Deletes

Posts and comments use soft deletes via `deleted_at` column:

```php
$post->deleted_at = new \DateTime();
$postsTable->save($post);
```

---

## User Journey Walkthrough

### 1. Landing → Login

**Route**: `/` → `LoginController::index`

**Flow**:
1. User visits `/` or `/login`
2. If already authenticated → redirect to `/dashboard`
3. If GET: render login form (`templates/Login/index.php`)
4. If POST with JSON:
   - Validate username/password
   - Authenticate via `AuthenticationComponent`
   - Track device session
   - Return JSON: `{ success: true, redirect: '/dashboard' }`
5. If POST with form:
   - Same auth flow
   - Redirect to `/dashboard` on success
   - Show Flash error on failure

### 2. Registration

**Route**: `/register` → `RegisterController::index`

**Flow**:
1. User submits registration form
2. Server validates:
   - Username uniqueness
   - Email uniqueness
   - Required fields
3. Create user entity with password hash (Argon2id)
4. Auto-login after registration
5. Redirect to `/dashboard`

### 3. Dashboard Feed

**Route**: `/dashboard` → `DashboardController::index`

**Flow**:
1. Main layout loads (`templates/layout/dashboard.php`)
2. Three column components fetched via AJAX:
   - `/dashboard/left-sidebar` → User info, navigation
   - `/dashboard/middle-column` → Posts feed
   - `/dashboard/right-sidebar` → Suggestions, birthdays
3. Middle column loads posts with pagination:
   ```php
   // Query includes:
   - Users (author info)
   - Reactions (with user's reaction highlighted)
   - Mentions (@mentions in post)
   - PostAttachments (images/videos)
   - Comments count
   ```

### 4. Creating a Post

**Route**: `/dashboard/posts/create` → `DashboardPostsController::create`

**Flow**:
1. User fills post composer (text, location, mentions)
2. JavaScript uploads media to Cloudinary
3. POST to `/dashboard/posts/create` with JSON:
   ```json
   {
     "content_text": "Hello world!",
     "location": "New York",
     "mentions": [1, 2, 3],
     "media": [{ "url": "...", "type": "image" }]
   }
   ```
4. Backend:
   - Creates post entity
   - Saves to `posts` table
   - Creates `post_attachments` entries
   - Creates `mentions` entries
   - Creates notifications for @mentions
5. Returns JSON with created post
6. Frontend appends new post to feed

### 5. Reacting to a Post

**Route**: `/dashboard/posts/react` → `DashboardReactionsController::react`

**Flow**:
1. User clicks reaction button (like/haha/love/wow/sad/angry)
2. POST JSON to `/dashboard/posts/react`:
   ```json
   {
     "target_type": "post",
     "target_id": 123,
     "reaction_type": "love"
   }
   ```
3. Backend logic:
   - If same reaction exists → delete (toggle off)
   - If different reaction → update
   - If no reaction → create new
4. Creates/removes notification for post owner
5. Returns updated counts and user's reaction

### 6. Following a User

**Route**: `/friends/follow` → `FriendsController::follow`

**Flow**:
1. User clicks "Follow" on profile/suggestion
2. POST to `/friends/follow`:
   ```json
   { "user_id": 456 }
   ```
3. Backend creates friendship record:
   ```php
   $friendshipsTable->follow($currentUserId, $followingId);
   ```
4. Creates follow notification
5. Returns success JSON

---

## Data Flow Examples

### Feed Pagination (Infinite Scroll)

```javascript
// dashboard.js
let start = 0;
const limit = 8;

function loadMorePosts(feedType = 'friends') {
    fetch(`/dashboard/middle-column?start=${start}&feed=${feedType}`)
        .then(r => r.text())
        .then(html => {
            document.getElementById('posts-list').innerHTML += html;
            start += limit;
        });
}

// Intersection Observer triggers loadMorePosts() when user scrolls near bottom
```

### Real-time Notifications (WebSocket)

```javascript
// websocket-client.js
const socket = io('http://localhost:3000');

socket.on('connect', () => {
    // Authenticate with token
    socket.emit('authenticate', { token: userWebSocketToken });
});

socket.on('notification', (data) => {
    // Show notification toast
    showToast(data.message);
    // Update badge count
    updateUnreadBadge(data.unreadCount);
});
```

---

## Common Development Tasks

### Running Migrations

```bash
# From backend container
docker-compose exec backend bin/cake migrations migrate
```

### Database Seeds

The database is seeded from `db/init-db.sql` on container startup. For additional seeds:

```bash
docker-compose exec backend php scripts/seed_users.php
```

### Viewing Logs

```bash
# All container logs
docker-compose logs -f

# Backend PHP logs
docker-compose logs -f backend

# MySQL logs
docker-compose logs -f db
```

### Running Tests

```bash
docker-compose exec backend bin/cake test
```

### Code Quality

```bash
# Code style check
docker-compose exec backend vendor/bin/phpcs --colors -p

# Auto-fix code style
docker-compose exec backend vendor/bin/phpcbf --colors -p
```

---

## Troubleshooting

### Container Won't Start

```bash
# Check logs
docker-compose logs

# Rebuild containers
docker-compose down
docker-compose build --no-cache
docker-compose up
```

### Database Connection Issues

1. Ensure MySQL container is running:
   ```bash
   docker-compose ps
   ```

2. Check database credentials match in `docker-compose.yaml` and `config/app.php`

3. Wait for MySQL to fully initialize (30-60 seconds on first run)

### WebSocket Connection Failed

1. Verify WebSocket container is running: `docker-compose ps`
2. Check WebSocket logs: `docker-compose logs websocket`
3. Ensure Redis is accessible from WebSocket container

### Upload Issues

1. Check Cloudinary configuration in `config/cloudinary.php`
2. Verify upload directory permissions: `backend/webroot/uploads/`
3. Check PHP upload limits in `php/php.ini`

### Session/Auth Issues

1. Clear browser cookies and session storage
2. Check Redis is running (sessions stored in Redis)
3. Verify `SESSION_DRIVER` in `config/app.php`

---

## API Quick Reference

### Required Headers for Authenticated Requests

```http
Cookie: welinked_session=...
X-Requested-With: XMLHttpRequest
Content-Type: application/json (for JSON endpoints)
```

### Response Format

Success:
```json
{
  "success": true,
  "data": { ... }
}
```

Error:
```json
{
  "success": false,
  "message": "Error description"
}
```

---

## License

MIT License - See LICENSE file for details.
