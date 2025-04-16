# Campaign Lead Manager

A Laravel application for managing campaigns and leads, featuring a robust bulk import functionality with validation and error reporting.

## Features

- Campaign management (CRUD operations)
- Lead management with campaign association
- Bulk import of leads from Excel/CSV files
- Advanced validation for phone numbers and emails
- Detailed error reporting for failed imports
- User-friendly Filament admin interface

## Requirements

- Docker & Docker Compose
- Git
- WSL2 (for Windows users)

## Installation & Setup

### Clone the Repository

```bash
git clone https://github.com/yourusername/campaign-lead-manager.git
cd campaign-lead-manager
```

### Using Laravel Sail (Docker)

#### Windows Users with WSL2

1. Make sure you have Docker Desktop and WSL2 installed
2. Start Docker Desktop
3. In your WSL2 terminal, navigate to your project directory

#### Start the Application

1. Copy the environment file:
```bash
cp .env.example .env
```

2. Start Docker containers:
```bash
./vendor/bin/sail up -d
```

> **Tip**: You can create a shell alias for the sail command to make it shorter:
> ```bash
> alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
> ```
> After setting this alias, you can simply use `sail` instead of `./vendor/bin/sail`

3. Install dependencies:
```bash
./vendor/bin/sail composer install
```

4. Generate application key:
```bash
./vendor/bin/sail artisan key:generate
```

5. Run migrations:
```bash
./vendor/bin/sail artisan migrate
```

6. Create an admin user:
```bash
./vendor/bin/sail artisan make:filament-user
```

## Using the Application

Once installation is complete, you can access the application:

1. Open your browser and navigate to: `http://localhost`
2. Log in using the credentials you created during setup
3. Use the Filament admin panel to manage campaigns and leads

## Bulk Import Feature

The application provides a powerful bulk import feature for leads:

1. Navigate to the Leads section
2. Click on the "Import Leads" button
3. Select a campaign from the dropdown
4. Upload an Excel (.xlsx) or CSV (.csv) file
5. The system will import valid leads and generate an error report for any invalid entries

### CSV/Excel Format

Your import file should include these columns:
- `name` - The lead's name
- `email` - A valid email address
- `phone_number` - Phone number in international format (e.g., +1234567890)

### Validation

The import process validates:
- Required fields (name, email, phone_number)
- Valid email format
- International phone number format (using Google's libphonenumber library)

If validation fails for any row, it will be skipped but valid rows will still be imported.

## Development

### Useful Commands

- Start Sail: `./vendor/bin/sail up -d`
- Stop Sail: `./vendor/bin/sail down`
- Run migrations: `./vendor/bin/sail artisan migrate`

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
