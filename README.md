# Library Management System

A Library Management System built with Symfony 7.2, Docker, PostgreSQL, and Vite.

## Getting Started

### Prerequisites

- [Docker](https://www.docker.com/get-started) and Docker Compose
- [Git](https://git-scm.com/downloads)

### Installation

1. Clone the repository:

```bash
git clone https://github.com/ValiusSloth/library-manager-test.git
cd library-manager-test
```

2. Start the Docker containers:

```bash
docker compose up -d
```

3. Install dependencies:

```bash
docker compose exec php composer install
docker compose exec php npm install
```

4. Build the frontend assets:

```bash
docker compose exec php npm run build
```

5. Set up the database:

```bash
docker compose exec php php bin/console doctrine:migrations:migrate
```

6. (Optional) Load sample data:

```bash
docker compose exec php php bin/console doctrine:fixtures:load
```

### Running the Application

The application will be available at:

- **Web Interface**: http://localhost:8080
- **Default Admin Login**: admin@admin.com / admin


## Testing

### Running Tests

Run all tests:

```bash
docker compose exec php php bin/phpunit
```

Run specific test categories:

```bash
# Run only unit tests
docker compose exec php php bin/phpunit tests/Unit

# Run only integration tests
docker compose exec php php bin/phpunit tests/Integration

# Run only E2E tests
docker compose exec php php bin/phpunit tests/E2E

# Run a specific test file
docker compose exec php php bin/phpunit tests/Unit/Entity/BookTest.php
```

## Development Workflow

1. **Start the development environment**:

```bash
docker compose up -d
```

2. **Watch for frontend changes**:

```bash
docker compose exec php npm run watch
```

3. **Access the PHP container**:

```bash
docker compose exec php bash
```

4. **Create a database migration after entity changes**:

```bash
docker compose exec php php bin/console make:migration
docker compose exec php php bin/console doctrine:migrations:migrate
```

5. **Rebuild the containers after Dockerfile changes**:

```bash
docker compose up -d --build
```