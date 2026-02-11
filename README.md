# WeLinked

A LinkedIn-style web application built with **CakePHP 5.3**, **Vue.js 3**, and **MySQL 8.0** using **MVC Architecture**.

## 🚀 Tech Stack

- **Backend**: CakePHP 5.3 (PHP 8.2+)
- **Frontend**: Vue.js 3 (CDN)
- **Database**: MySQL 8.0
- **Web Server**: Nginx
- **Architecture**: MVC (Model-View-Controller)

## 📁 Project Structure

```
WeLinked/
├── Docker-compose.yaml          # Docker orchestration
├── backend/                     # CakePHP 5.x application
│   ├── src/
│   │   ├── Controller/          # Controllers (Business logic)
│   │   │   ├── AppController.php
│   │   │   ├── UsersController.php
│   │   │   ├── LoginController.php
│   │   │   ├── RegisterController.php
│   │   │   └── PagesController.php
│   │   ├── Model/               # Models (Database layer)
│   │   │   ├── Entity/          # Entity classes
│   │   │   ├── Table/           # Table classes
│   │   │   └── Behavior/        # Custom behaviors
│   │   ├── View/                # View helpers and cells
│   │   │   ├── AppView.php
│   │   │   └── AjaxView.php
│   │   ├── Application.php      # Application bootstrap
│   │   └── Console/             # CLI commands
│   ├── templates/               # View templates (Vue.js integration)
│   │   ├── Login/
│   │   │   └── index.php        # Vue.js login page
│   │   ├── Register/
│   │   │   └── index.php        # Vue.js register page
│   │   ├── Users/
│   │   │   └── dashboard.php    # Vue.js dashboard
│   │   ├── layout/
│   │   │   ├── default.php      # Main layout with Vue CDN
│   │   │   ├── login.php        # Login layout
│   │   │   └── error.php        # Error layout
│   │   └── element/             # Reusable view elements
│   ├── config/                  # Configuration files
│   │   ├── app.php              # Main app config
│   │   ├── routes.php           # URL routing
│   │   └── bootstrap.php        # Bootstrap configuration
│   ├── webroot/                 # Public assets (CSS, JS, images)
│   │   ├── index.php            # Application entry point
│   │   └── css/                 # Stylesheets
│   ├── composer.json            # PHP dependencies
│   └── logs/                    # Application logs
├── php/                         # PHP-FPM Docker configuration
│   ├── Dockerfile               # PHP 8.2-FPM with extensions
│   └── php.ini                  # PHP settings
├── nginx/                       # Nginx configuration
│   └── conf.d/
│       └── default.conf         # Server config
└── db/                          # Database setup
    └── init-db.sql              # Initial schema & test data
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

### Custom Routes (defined in `backend/config/routes.php`)
- `/` → Login page (default)
- `/login` → Login page
- `/register` → Registration page
- `/dashboard` → User dashboard
- `/logout` → Logout

### Convention-based Routes (automatic)
- `/users/dashboard` → Same as `/dashboard`
- `/users/{action}` → UsersController actions
- `/{controller}/{action}` → Standard CakePHP routing

## 🏗️ MVC Architecture

### Model (`backend/src/Model/`)
- **Table classes**: Handle database operations, queries, and associations
- **Entity classes**: Represent individual database records with validation
- **Behaviors**: Reusable model functionality
- Password hashing and data validation

### View (`backend/templates/`)
- `.php` template files (CakePHP 5.x)
- Vue.js 3 integrated via CDN
- Reactive UI components
- Layouts for consistent page structure

### Controller (`backend/src/Controller/`)
- Handles HTTP requests and routing
- Processes business logic
- Returns JSON for Vue.js AJAX or renders views
- Authentication and authorization

## 🐳 Docker Services

| Service | Container Name | Port | Description |
|---------|----------------|------|-------------|
| nginx | welinked-nginx-1 | 80, 443 | Web server & reverse proxy |
| backend | welinked-backend | 9000 | PHP 8.2-FPM application server |
| db | welinked-db | 3306 | MySQL 8.0 database |

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
Edit `backend/config/routes.php` within the scope function:
```php
$builder->connect('/your-route', [
    'controller' => 'YourController',
    'action' => 'yourAction'
]);
```

### Creating New MVC Components

**Table (Model)**: `backend/src/Model/Table/YourModelTable.php`
**Entity**: `backend/src/Model/Entity/YourModel.php`
**Controller**: `backend/src/Controller/YourController.php`
**View Template**: `backend/templates/YourController/action.php`

### Vue.js Integration
Vue 3 is loaded via CDN in `backend/templates/layout/default.php`. Each view template can create a Vue app instance using the Composition API or Options API.

### CakePHP 5.x Key Features
- Modern PHP 8.2+ features (typed properties, attributes)
- PSR-7 HTTP message implementation
- Improved authentication with CakePHP Authentication plugin
- Better dependency injection and service containers
- Enhanced migration system

## 📦 Database Schema

See `db/init-db.sql` for the current schema. The `users` table is created automatically on first run.

## 🔒 Security Notes

- Passwords are hashed using PHP's `password_hash()`
- Change default passwords in production
- Update `welinked@!password` in `Docker-compose.yaml` for production

## 📚 Resources

- [CakePHP 5.x Documentation](https://book.cakephp.org/5/en/index.html)
- [CakePHP Authentication Plugin](https://book.cakephp.org/authentication/3/en/index.html)
- [Vue.js 3 Documentation](https://vuejs.org/)
- [MySQL 8.0 Reference](https://dev.mysql.com/doc/refman/8.0/en/)
- [PHP 8.2 Documentation](https://www.php.net/releases/8.2/en.php)
