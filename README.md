# Project Management API

A robust Laravel-based API for project management with dynamic attributes and timesheet tracking.

## Features

- 🔐 User Authentication with Laravel Passport
- 📊 Project Management with Dynamic Attributes (EAV Pattern)
- ⏱️ Timesheet Tracking
- 🔍 Flexible Filtering System
- ✨ Professional Form Request Validation
- 📝 Comprehensive API Documentation
- 🧪 Automated Testing with SQLite

## Requirements

- PHP >= 8.1
- MySQL >= 8.0 (for development)
- SQLite (for testing)
- Composer
- Laravel 10.x

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd project-directory
```

2. Install dependencies:
```bash
composer install
```

3. Configure environment:
```bash
cp .env.example .env
cp .env.testing.example .env.testing
# Edit .env with your database credentials
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Run migrations and install Passport:
```bash
php artisan migrate
php artisan passport:install
```

6. Seed the database (optional):
```bash
php artisan db:seed
```

## Testing

The project uses SQLite in-memory database for testing to ensure:
- Fast test execution
- Isolation from development database
- No additional database setup required
- Consistent test environment across different machines

### Running Tests

Run all tests:
```bash
php artisan test
```

Run specific test suite:
```bash
php artisan test tests/Feature/API/AttributeControllerTest.php
php artisan test tests/Feature/API/ProjectControllerTest.php
```

### Test Environment

The testing environment is configured in `.env.testing` and uses:
- SQLite in-memory database
- Array drivers for cache and session
- Simplified mailer configuration
- Faster password hashing rounds

## API Documentation

### Authentication Endpoints

#### Register User
```http
POST /api/register
Content-Type: application/json

{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "password": "password",
    "password_confirmation": "password"
}
```
Validation Rules:
- first_name: Required, string, max 255 chars
- last_name: Required, string, max 255 chars
- email: Required, valid email, unique in users table
- password: Required, min 8 chars, must be confirmed

#### Login
```http
POST /api/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password"
}
```
Validation Rules:
- email: Required, valid email
- password: Required

#### Logout
```http
POST /api/logout
Authorization: Bearer {token}
```

### Project Management

#### List Projects
```http
GET /api/projects
Authorization: Bearer {token}

Query Parameters:
- filters[name]=ProjectName (partial match supported)
- filters[status]=active
- filters[custom_attribute]=value (partial match supported)
```

#### Create Project
```http
POST /api/projects
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Project Name",
    "status": "active",
    "user_ids": [1, 2],
    "attributes": {
        "department": "IT",
        "start_date": "2024-03-01"
    }
}
```
Validation Rules:
- name: Required, string, max 255 chars
- status: Required, one of: active, completed, on_hold, cancelled
- user_ids: Optional array of existing user IDs (current user always included)
- attributes: Optional array of attribute key-value pairs

#### Update Project
```http
PUT /api/projects/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Updated Name",
    "status": "completed",
    "user_ids": [1, 2, 3],
    "attributes": {
        "department": "HR"
    }
}
```
Validation Rules:
- All fields are optional for partial updates
- Same validation rules as Create Project when fields are provided
- Current user remains in project's user list

### Timesheet Management

#### Create Timesheet Entry
```http
POST /api/timesheets
Authorization: Bearer {token}
Content-Type: application/json

{
    "project_id": 1,
    "task_name": "Development",
    "date": "2024-03-02",
    "hours": 8
}
```
Validation Rules:
- project_id: Required, must exist in projects table
- task_name: Required, string, max 255 chars
- date: Required, valid date, not in future
- hours: Required, numeric, between 0.5 and 24

#### Update Timesheet Entry
```http
PUT /api/timesheets/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "task_name": "Updated Task",
    "hours": 6
}
```
Validation Rules:
- All fields are optional for partial updates
- Same validation rules as Create Timesheet when fields are provided

### Dynamic Attributes

#### Create Attribute
```http
POST /api/attributes
Authorization: Bearer {token}
Content-Type: application/json

{
    "project_id": 1,
    "name": "department",
    "type": "select",
    "options": ["IT", "HR", "Finance"],
    "value": "IT"
}
```
Validation Rules:
- project_id: Required, must exist in projects table
- name: Required, string, max 255 chars, unique within project
- type: Required, one of: string, number, date, select
- options: Required for select type, array of strings
- value: Required, type-specific validation:
  - string: string value
  - number: numeric value
  - date: valid date format
  - select: must be one of the provided options

#### Update Attribute
```http
PUT /api/attributes/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "department",
    "value": "Marketing",
    "options": ["IT", "HR", "Finance", "Marketing"]
}
```
Validation Rules:
- name: Optional, unique within project except current attribute
- type: Optional, one of: string, number, date, select
- options: Required if type is select
- value: Optional, must match type-specific validation

## Authorization

The API implements the following authorization rules:
- Users can only access projects they are members of
- Project listing only shows projects the user has access to
- Unauthorized access returns 404 Not Found for better security
- Current user is always maintained as a project member

## Error Handling

The API returns standardized error responses:

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "field_name": [
            "Error message"
        ]
    }
}
```

HTTP Status Codes:
- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found (or Unauthorized)
- 422: Validation Error
- 500: Server Error

## Test Credentials

```
Email: test@example.com
Password: password
```

## Development Team

👨‍💻 Author: Fahed
📧 Contact: [Contact Information]

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
