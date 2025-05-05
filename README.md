# 🧵 Tailorapp API – Setup Guide

## 📦 Requirements

- **PHP Version**: 8.1 or later  
- **Database**: MySQL

---

## ⚙️ Step-by-Step Setup Instructions

### 1. Create Local Database

Create a MySQL database named:

```sql
tailorinch
```

---

### 2. Configure Environment File

- Duplicate the `.env.example` file and rename it to `.env`:

```bash
cp .env.example .env
```

- Update the following variables with your local database configuration:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tailorinch
DB_USERNAME=root
DB_PASSWORD=
```

> 💡 Make sure your MySQL server is running and accessible.

---

### 3. Install PHP Dependencies

Install all required PHP packages using Composer:

```bash
composer install
```

---

### 4. Generate Application Key

Run the following Artisan command:

```bash
php artisan key:generate
```

---

### 5. Run Database Migrations

Migrate the database schema:

```bash
php artisan migrate
```

---

### 6. Generate Swagger API Documentation (Optional)

If you're using Swagger for API documentation, generate it with:

```bash
php artisan l5-swagger:generate
```

---

## 📊 Filament Admin Dashboard Setup

### 1. Install Filament

Add Filament to your Laravel project:

```bash
composer require filament/filament
```

---

### 2. Run Filament Migrations

Apply any additional migrations needed for Filament:

```bash
php artisan migrate
```

---

### 3. Create Filament Admin User

Create an admin user for accessing the Filament dashboard:

```bash
php artisan make:filament-user
```

Follow the prompt to set user credentials.

---

### 4. Access the Filament Dashboard

Once the user is created, visit:

```
http://tailorinch.com/admin
```

> 🛠️ Ensure your local development server is running.

---

