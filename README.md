# TaskManage

A feature-rich Task Management system built on top of Laravel, offering robust handling of tasks including creation, modification, validation, and file attachments.

## Setup Instructions

To get the Task Management system up and running on your local machine, follow these steps:

1. **Clone the repository** (or navigate to your working directory):
   ```bash
   cd TaskManage
   ```

2. **Install PHP Dependencies**:
   ```bash
   composer install
   ```

3. **Install Node.js Dependencies**:
   ```bash
   npm install
   ```

4. **Environment Setup**:
   Copy the example environment file and set up the required configurations.
   ```bash
   cp .env.example .env
   ```

5. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

6. **Database Migration**:
   We use SQLite by default as pre-configured in `.env.example`.
   Run the database migrations to set up the necessary tables:
   ```bash
   php artisan migrate
   ```

7. **Compile Frontend Assets**:
   ```bash
   npm run build
   # or for development
   npm run dev
   ```

8. **Start the Application**:
   Serve the application using Artisan:
   ```bash
   php artisan serve
   ```
   Alternatively, you can access the application via Laravel Herd depending on your local setup.

## Technologies Used

* **PHP 8.3**
* **Laravel 13.0** - The core PHP framework
* **mySql** - Default relational database setup
* **Tailwind CSS 4.0** - Utility-first CSS framework for styling UI components
* **Vite** - Frontend build tool and asset bundler
* **Pest PHP** - PHP Testing Framework

## Assumptions & Decisions Made

* **Database Strategy**: Chose mySql as the default database for ease of local development and a painless initialization process.
* **Attachment Storage**: Tasks support file attachments. The local public disk (`storage/app/public`) is utilized for handling multipart file uploads, leveraging Laravel's filesystem conventions.
* **UI/UX Choices**: Blade templates mapped with Tailwind CSS are used to deliver a clean, aesthetic, and responsive interface for operations like listing tasks and managing creation/editing forms.
* **Testing Framework**: We adopted Pest PHP to write cleaner, more expressive tests compared to traditional PHPUnit configurations. 
* **State Management**: The application strictly follows Laravel MVC structures. Redirection and basic session states are employed (e.g., flashing success states after task creation/updates).

## Testing Approach

Application robustness is maintained through a robust suite of automated feature tests validating HTTP endpoints, database integrations, and attachment behaviors.

1. **Core Utilities**: Tests are written using **Pest PHP** integrating native Laravel testing APIs mapped under `tests/Feature/TaskTest.php`.
2. **Feature Coverage**: 
   - **Page Rendering**: Validating routing paths (`tasks.index`, `tasks.create`, `tasks.edit`, `tasks.show`) reliably return HTTP `200` statuses.
   - **CRUD Operations**: Ensuring proper payload handling on task inserts, updates, and deletes, combined with comprehensive assertions (`assertDatabaseHas`, `assertDatabaseMissing`).
   - **File Uploads**: Meticulously verifying multipart form uploads using `Storage::fake('public')` alongside `UploadedFile::fake()` to guarantee attachments persist perfectly on disk while their references are stored in the database.
3. **Test Execution**: 
   To execute the system tests locally, run:
   ```bash
   php artisan test
   ```
   Or call Pest directly:
   ```bash
   ./vendor/bin/pest
   ```

## API Documentation

The application provides a RESTful API for managing tasks. All API requests should be directed to the `/api` prefix and responses are returned in JSON format.

### Endpoints

* **`GET /api/tasks`** - Retrieve a paginated list of tasks (10 per page).
* **`POST /api/tasks`** - Create a newly created task.
* **`GET /api/tasks/{task}`** - Retrieve a specific task by its ID.
* **`PUT/PATCH /api/tasks/{task}`** - Update an existing task.
* **`DELETE /api/tasks/{task}`** - Delete a specific task. Returns `204 No Content`.

### Resource Format

The API utilizes Laravel's Eloquent API Resources (`TaskResource`) to consistently format the JSON responses for individual models and collections.
