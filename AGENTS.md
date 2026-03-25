# AGENTS.md - Codebase Guidelines for AI Agents

## Project Overview

This is a **PHP-based Car Repair Shop Management System** (`Sistema_Gestion_Taller_Mecanico`). It uses:
- **PHP** (vanilla, no framework)
- **MySQL** database (via PDO)
- **Composer** for dependency management
- **Dompdf** for PDF generation
- **PhpSpreadsheet** for Excel exports

---

## 1. Build / Lint / Test Commands

### PHP Syntax Check
```bash
# Check a single PHP file for syntax errors
php -l path/to/file.php

# Check all PHP files in a directory
find . -name "*.php" -exec php -l {} \;
```

### Run Single Test (Manual Testing)
Since this is a vanilla PHP project without a test framework:
```bash
# Test a specific file by including it (ensure database is available)
php -r "require 'conexion/bd.php'; echo 'Database OK\n';"
```

### Code Quality Tools (Optional)
```bash
# Install PHP_CodeSniffer for style checking
composer require --dev squizlabs/php_codesniffer

# Run CodeSniffer on a file
vendor/bin/phpcs --standard=PSR12 path/to/file.php

# Fix auto-fixable issues
vendor/bin/phpcbf path/to/file.php

# Install PHPStan for static analysis
composer require --dev phpstan/phpstan

# Run static analysis
vendor/bin/phpstan analyse path/to/file.php --level=max
```

### Composer Commands
```bash
# Install dependencies
composer install

# Update dependencies
composer update

# Dump autoloader
composer dump-autoload
```

### Running the Application
- Start Apache via XAMPP
- Access via `http://localhost/Sistema_Gestion_Taller_Mecanico`
- Default MySQL credentials in `conexion/bd.php`: `root` / no password

---

## 2. Code Style Guidelines

### General Principles

- **No PHP closing tag `?>`** at end of pure PHP files (prevents whitespace issues)
- **Always use `<?php`** at the start of PHP files
- Use **4 spaces** for indentation (not tabs)
- Maximum line length: **120 characters**
- Use **UTF-8** encoding

### File Organization

```
/conexion/          # Database and session handling
/controladores/     # Business logic (CRUD operations)
/admin/             # Admin dashboard pages
/mecanico/          # Mechanic dashboard pages
/recepcionista/     # Receptionist dashboard pages
/templates/         # Reusable UI components (header, footer, sidebar)
/ReportesPDF/       # PDF report generation
/ReportesExcel/     # Excel export functionality
/js/                # JavaScript files
/autorizacion/      # Authentication/authorization logic
```

### Naming Conventions

| Element | Convention | Example |
|---------|------------|---------|
| PHP files | snake_case.php | `crear_orden_servicio.php` |
| Classes | PascalCase | `UserService` |
| Functions | snake_case | `get_user_by_id()` |
| Variables | snake_case | `$email`, `$id_usuario` |
| Constants | UPPER_SNAKE_CASE | `MAX_INTENTOS`, `DB_HOST` |
| Database tables | snake_case | `usuarios`, `orden_servicios` |
| Database columns | snake_case | `id_usuario`, `fecha_creacion` |

### PHP Code Style

#### Variables and Data Types
```php
// Good
$email = trim($_POST["email"]);
$password = trim($_POST["password"]);
$id = (int) $_POST["id"];  // Explicit casting for integers
$precio = (float) $_POST["precio"];

// Avoid
$email = $_POST["email"];  // Always sanitize input
```

#### Database Queries (PDO)
```php
// Always use prepared statements
$sql = $conexion->prepare("SELECT id, nombre FROM usuarios WHERE email = :email");
$sql->bindParam(":email", $email, PDO::PARAM_STR);
$sql->execute();
$usuario = $sql->fetch(PDO::FETCH_OBJ);

// Use PARAM_INT for numeric values
$sql->bindParam(":id", $id, PDO::PARAM_INT);
```

#### Control Structures
```php
// Use strict comparison where possible
if ($_SERVER["REQUEST_METHOD"] === "POST") { ... }

// Use isset() with early returns
if (!isset($_POST["email"], $_POST["password"])) {
    $_SESSION["errores"] = ["Faltan datos requeridos"];
    header("Location: login.php");
    exit();
}

// Switch statements for role-based routing
switch ($usuario->rol) {
    case "admin":
        header("Location: admin/dash_admin.php");
        exit();
    case "mecanico":
        header("Location: mecanico/dash_mecanico.php");
        exit();
    default:
        header("Location: acceso_denegado.php");
        exit();
}
```

#### Error Handling
```php
// Database errors - log and show user-friendly message
try {
    $sql->execute();
} catch (PDOException $e) {
    error_log("Error en crear_orden.php: " . $e->getMessage());
    $_SESSION["errores"] = ["Error al procesar la solicitud. Intente nuevamente."];
    header("Location: formulario.php");
    exit();
}

// Form validation - collect all errors
$errores = [];
if (empty($email)) {
    $errores[] = "El email es obligatorio";
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errores[] = "El email no es válido";
}

if (!empty($errores)) {
    $_SESSION["errores"] = $errores;
    header("Location: formulario.php");
    exit();
}
```

### Security Guidelines

1. **CSRF Protection**: Include CSRF tokens in all POST forms
```php
// Generate token (in session initialization)
if (!isset($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// In forms
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

// Validate on submission
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Acceso no autorizado. Token CSRF inválido.');
}
```

2. **Session Security**: Use secure session settings (already configured in `conexion/session.php`)
3. **Input Sanitization**: Always use `trim()` and `htmlspecialchars()` for user input
4. **Password Hashing**: Use `password_hash()` and `password_verify()`
5. **SQL Injection Prevention**: Always use prepared statements

### Database Conventions

- Use **InnoDB** engine for all tables (supports transactions)
- Always define **PRIMARY KEY** on all tables
- Use **utf8mb4** charset for proper Unicode support
- Add **timestamps** (`created_at`, `updated_at`) to track record changes
- Use **foreign keys** with `ON DELETE CASCADE` where appropriate

### Session and Flash Messages

```php
// Store errors
$_SESSION["errores"] = ["Mensaje de error"];

// Store success messages
$_SESSION["exito"] = "Operación exitosa";

// Persist form data on error
$_SESSION["datos_form"] = $_POST;

// Clear after displaying
unset($_SESSION["errores"]);
unset($_SESSION["exito"]);
```

### Import/Require Statements

```php
// Use require_once for critical dependencies
require_once '../conexion/session.php';
require_once '../conexion/bd.php';

// Path structure: go up one level (..) then navigate to target
require_once '../controladores/crear_orden_servicio.php';
```

### Comments

- Add brief comments for complex logic or business rules
- Use Spanish comments (project language)
- Document all controller actions with input/output expectations

---

## Common Patterns

### Controller Pattern (CRUD)
```php
<?php
require_once '../conexion/session.php';
require_once '../conexion/bd.php';

// Validate request method and required parameters
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['campo1'])) {
    
    // CSRF validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Acceso no autorizado');
    }

    // Sanitize input
    $campo1 = trim($_POST['campo1']);

    // Validate
    $errores = [];
    if (empty($campo1)) {
        $errores[] = "Campo1 es obligatorio";
    }

    if (!empty($errores)) {
        $_SESSION['errores'] = $errores;
        header("Location: formulario.php");
        exit();
    }

    // Execute
    try {
        $sql = $conexion->prepare("INSERT INTO tabla (campo1) VALUES (:campo1)");
        $sql->bindParam(':campo1', $campo1, PDO::PARAM_STR);
        $sql->execute();

        $_SESSION['exito'] = "Registro creado correctamente";
        header("Location: lista.php");
        exit();
    } catch (PDOException $e) {
        error_log("Error en crear: " . $e->getMessage());
        $_SESSION['errores'] = ["Error al procesar la solicitud"];
        header("Location: formulario.php");
        exit();
    }
}

header("Location: lista.php");
exit();
```

### Role-Based Access Control
```php
// Check user role before allowing access
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../acceso_denegado.php");
    exit();
}
```

---

## Key Files Reference

| File | Purpose |
|------|---------|
| `conexion/bd.php` | Database connection (PDO) |
| `conexion/session.php` | Session configuration with security settings |
| `autorizacion/auth.php` | Authentication helpers |
| `login.php` | Main login page with rate limiting |
| `templates/header.php` | Common HTML head, nav, CSS includes |
| `templates/sidebar.php` | Role-based navigation menu |
| `templates/footer.php` | Common footer, JS includes |
