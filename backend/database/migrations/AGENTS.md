# DATABASE MIGRATIONS

## OVERVIEW

Database schema definitions for restaurant violation tracking system.

## MIGRATION FILES

| Table                  | File                                                      | Purpose                                |
| ---------------------- | --------------------------------------------------------- | -------------------------------------- |
| users                  | 0001_01_01_000000_create_users_table.php                  | User accounts with Fortify 2FA columns |
| cache                  | 0001_01_01_000001_create_cache_table.php                  | Laravel cache storage                  |
| jobs                   | 0001_01_01_000002_create_jobs_table.php                   | Queue job storage                      |
| violation_types        | 2026_02_13_055634_create_violation_types_table.php        | Types of restaurant violations         |
| violations             | 2026_02_13_073836_create_violations_table.php             | Restaurant violation records           |
| violation_details      | 2026_02_13_075539_create_violation_details_table.php      | Violation evidence/details             |
| cameras                | 2026_02_13_065611_create_cameras_table.php                | Surveillance cameras                   |
| personal_access_tokens | 2026_02_13_080658_create_personal_access_tokens_table.php | Sanctum API tokens                     |

## CONVENTIONS

- Use `php artisan make:migration` for all new migrations
- Follow naming: `YYYY_MM_DD_HHMMSS_create_table_name_table.php`
- Always define foreign keys and indexes
- Use `$table->timestamps()` for created_at/updated_at
- Use `$table->id()` for auto-increment primary keys
- Use `$table->enum()` for fixed value sets (status, type)
- Use `$table->nullable()` for optional columns
- Always implement `up()` and `down()` methods

## DOMAIN MODELS

- User - Auth with 2FA support
- ViolationType - Categories of violations
- Violation - Restaurant violation records
- ViolationDetail - Evidence/photos per violation
- Camera - Surveillance devices
