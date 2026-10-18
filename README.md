# Multi-Tenant URL Shortener Platform

A robust, multi-tenant URL shortening and redirection service built with **Laravel 11**, **PHP 8.2+**, and **Bootstrap 5**.

---

## Architecture & System Design

The system implements a tenant-isolated data structure:
- **Tenancy**: Each user belongs to a `Company` (`company_id`).
- **URL Scoping**: URLs belong to both a `Company` and a `User` (`user_id`).
- **Role Isolation & Access Matrix**:
  - `SuperAdmin`: System oversight. Can view all short URLs across every company. Can invite an Admin into a new company or assign them to an existing company. **Cannot** create short URLs.
  - `Admin`: Company manager. Can create short URLs. Can view all short URLs created within their company. Can invite new `Admin` or `Member` users into their own company.
  - `Member`: Standard user. Can create short URLs. Can only view URLs created by themselves.
- **Public URL Resolution**: All generated short URLs are publicly resolvable without authentication, incrementing a click counter upon redirect.

---

## Requirements

- **PHP**: `^8.2` (Extensions required: `bcmath`, `curl`, `fileinfo`, `mbstring`, `openssl`, `pdo_mysql` or `pdo_sqlite`, `zip`)
- **Composer**: `2.2+`
- **Database**: MySQL 8.0+ / MariaDB or SQLite

---

## Local Setup & Installation

### 1. Clone the Repository
```bash