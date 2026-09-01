# bibliotrack

a library management system for public libraries — catalog, loans, inventory and reporting, with separate dashboards for admins and readers.

## what it does

- book catalog and inventory management
- loan (prestamos) tracking with pending-request handling
- reader-facing dashboard: browse, request and track loans
- admin dashboard with reports and stats
- user and role management

## stack

PHP (custom MVC) · MySQL · Docker

## running it

`docker compose up` — app on http://localhost:8080, phpMyAdmin on http://localhost:8082

## license

MIT — see [LICENSE.md](./LICENSE.md)
