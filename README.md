# PWD-Application-System
An application system for PWD

## Deployment

1. Copy `.env.example` to `.env` and update secrets if needed.
2. Build and run with Docker:
   ```bash
   docker compose up --build
   ```
3. Visit `http://localhost:8080`.

## Notes

- The Docker image now installs `pdo_pgsql` and `pgsql` at build time.
- `config/db.php` supports `DB_PASSWORD`, `DB_PASS`, and `DATABASE_URL`.
- Use `docker compose down -v` to remove containers and the database volume.
