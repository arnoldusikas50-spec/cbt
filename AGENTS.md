# AGENTS.md - CBT (Computer-Based Test) System

## Stack & Framework
- **Framework**: CodeIgniter 3 (PHP MVC)
- **Database**: MySQL/MariaDB (mysqli driver)
- **Environment**: Set via `CI_ENV` (development/testing/production)

## Project Structure
- `application/` - Main app code (controllers, models, views, config)
- `system/` - CodeIgniter core (do not modify)
- `assets/` - Static files (CSS, JS, images)
- `db/` - SQL schema files (db.sql, document_types.sql)
- `installer/` - First-time setup wizard

## Code Conventions
- Controllers: PascalCase extending `CI_Controller` (e.g., `Auth`, `Cbtjadwal`)
- Models: PascalCase with `_model` suffix extending `CI_Model` (e.g., `Cbt_model`)
- Always start files with `defined('BASEPATH') OR exit('No direct script access allowed');`
- Use `$this->db` for Query Builder, `$this->load->model()` for models
- JSON responses via `$this->output->set_content_type('application/json')`

## Database
- Config: `application/config/database.php`
- Default DB: `sas` on localhost; uses Query Builder (`$query_builder = TRUE`)

## No Automated Tests
- No PHPUnit or test suite configured; test manually via browser/API
