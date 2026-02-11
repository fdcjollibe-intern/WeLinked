# WeLinked

A LinkedIn-style web application built with **CakePHP 2.0**, **Vue.js 3**, and **MySQL 8.0** using **MVC Architecture**.

## 🚀 Tech Stack

- **Backend**: CakePHP 2.0 (PHP 7.4)
- **Frontend**: Vue.js 3 (CDN)
- **Database**: MySQL 8.0
- **Web Server**: Nginx
- **Architecture**: MVC (Model-View-Controller)

## 📁 Project Structure

```
WeLinked/
├── Docker-compose.yaml          # Docker orchestration
├── backend/                     # CakePHP application
│   ├── app/
│   │   ├── Controller/          # Controllers (Business logic)
│   │   │   ├── UsersController.php
│   │   │   └── PagesController.php
│   │   ├── Model/               # Models (Database layer)
│   │   │   └── User.php
│   │   ├── View/                # Views (Frontend templates)
│   │   │   ├── Users/
│   │   │   │   ├── login.ctp    (Vue.js login page)
│   │   │   │   └── dashboard.ctp (Vue.js dashboard)
│   │   │   └── Layouts/
│   │   │       ├── default.ctp  (Main layout with Vue CDN)
│   │   │       └── error.ctp
│   │   ├── Config/              # Configuration files
│   │   │   ├── routes.php       (URL routing)
│   │   │   └── database.php     (DB connection)
│   │   └── webroot/             # Public assets (CSS, JS, images)
│   └── lib/                     # CakePHP core library
├── php/                         # PHP-FPM Docker configuration
│   ├── Dockerfile               (PHP 7.4 with extensions)
│   └── php.ini                  (PHP settings)
├── nginx/                       # Nginx configuration
│   └── conf.d/
│       └── default.conf         (Server config)
└── db/                          # Database setup
    └── init-db.sql              (Initial schema & test data)
```

## 🔧 Setup & Installation

### Prerequisites
- Docker Desktop
- Git

### Quick Start

```bash
# 1. Clone the repository
git clone <repository-url>
cd WeLinked

# 2. Start Docker containers
docker-compose up -d --build

# 3. Wait for containers to initialize (10-15 seconds)
docker-compose ps

# 4. Access the application
open http://localhost/login
```

## 🔐 Test Accounts

| Username | Password | Email |
|----------|----------|-------|
| admin | password123 | admin@welinked.com |
| testuser | password123 | test@welinked.com |

## 📍 Routes

### Custom Routes (defined in `backend/app/Config/routes.php`)
- `/login` → Login page
- `/dashboard` → User dashboard
- `/logout` → Logout

### Convention-based Routes (automatic)
- `/users/login` → Same as `/login`
- `/users/dashboard` → Same as `/dashboard`
- `/users/logout` → Same as `/logout`

## 🏗️ MVC Architecture

### Model (`backend/app/Model/`)
- Handles database operations
- Validates data
- Hashes passwords

### View (`backend/app/View/`)
- `.ctp` files (CakePHP Templates)
- Vue.js injected via CDN
- Reactive UI components

### Controller (`backend/app/Controller/`)
- Handles HTTP requests
- Processes business logic
- Returns JSON for Vue.js or renders views

## 🐳 Docker Services

| Service | Container Name | Port | Description |
|---------|----------------|------|-------------|
| nginx | welinked-nginx-1 | 80, 443 | Web server & reverse proxy |
| backend | welinked-backend | 9000 | PHP-FPM application server |
| db | welinked-db | 3306 | MySQL database |

## 🛠️ Common Commands

```bash
# View running containers
docker-compose ps

# View logs
docker-compose logs -f

# Restart all services
docker-compose restart

# Stop all services
docker-compose down

# Stop and remove volumes (fresh start)
docker-compose down -v

# Access MySQL database
docker exec -it welinked-db mysql -uwelinked -p'welinked@!password' welinked_db

# Access backend container shell
docker exec -it welinked-backend sh
```

## 🌐 URLs

- **Application**: http://localhost
- **Login**: http://localhost/login
- **Dashboard**: http://localhost/dashboard
- **MySQL**: localhost:3306

## 📝 Development Notes

### Adding New Routes
Edit `backend/app/Config/routes.php`:
```php
Router::connect('/your-route', array(
    'controller' => 'your_controller',
    'action' => 'your_action'
));
```

### Creating New MVC Components

**Model**: `backend/app/Model/YourModel.php`
**Controller**: `backend/app/Controller/YourController.php`
**View**: `backend/app/View/YourController/action.ctp`

### Vue.js Integration
Vue 3 is loaded via CDN in `backend/app/View/Layouts/default.ctp`. Each view can create a Vue app instance.

## 📦 Database Schema

See `db/init-db.sql` for the current schema. The `users` table is created automatically on first run.

## 🔒 Security Notes

- Passwords are hashed using PHP's `password_hash()`
- Change default passwords in production
- Update `welinked@!password` in `Docker-compose.yaml` for production

## 📚 Resources

- [CakePHP 2.x Documentation](https://book.cakephp.org/2.0/en/index.html)
- [Vue.js 3 Documentation](https://vuejs.org/)
- [MySQL 8.0 Reference](https://dev.mysql.com/doc/refman/8.0/en/)
