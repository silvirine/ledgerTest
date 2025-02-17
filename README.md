# Ledger Test Assignment

This project is a Symfony-based API for managing wallets, ledger entries, and transactions. It uses Docker for local development, PostgreSQL as the database, and includes comprehensive API documentation with Swagger (via NelmioApiDocBundle). The application supports multiple currencies by including a currency field in the Wallet entity.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Project Structure](#project-structure)
- [Installation & Setup](#installation--setup)
- [Docker Usage](#docker-usage)
- [Database Migrations](#database-migrations)
- [Load Testing](#load-testing)

## Features

- **Wallet Management:** Create, retrieve, update, and delete wallets with a balance and currency.
- **Ledger Entries:** Record individual ledger entries linked to a wallet and grouped by a transaction.
- **Transaction Management:** Create, retrieve, update, and delete transactions, each grouping one or more ledger entries.
- **Swagger Documentation:** API documentation is available at `/api/doc` (JSON at `/api/doc.json`).
- **Dockerized Environment:** All components run in Docker containers (PHP, PostgreSQL, Nginx).

## Requirements

- Docker and Docker Compose
- PHP 8.3 (via Docker)
- PostgreSQL 15 (via Docker)
- Node.js (v18+ for load testing with Artillery)
- Composer

## Project Structure

/project-root ├── docker-compose.yml # Docker Compose configuration ├── Dockerfile # PHP container Dockerfile ├── docker/ │ └── nginx/ │ └── default.conf # Nginx configuration ├── migrations/ # Doctrine migrations ├── src/ │ ├── Controller/ # API controllers (WalletController, LedgerController, TransactionController) │ ├── Entity/ # Doctrine entities (Wallet, Ledger, Transaction) │ ├── Repository/ # Doctrine repository classes │ └── Serializer/ # Custom serializer helpers (e.g., circular reference handler) ├── tests/ # Functional tests (e.g., WalletControllerTest, LedgerControllerTest, TransactionControllerTest) ├── config/ │ ├── packages/ │ │ ├── doctrine.yaml │ │ ├── nelmio_api_doc.yaml # API documentation configuration │ │ └── serializer.yaml │ └── routes/ │ └── annotations.yaml ├── phpunit.xml.dist # PHPUnit configuration ├── README.md # This file └── .env, .env.test 


## Installation & Setup


## Installation & Setup

1. **Clone the Repository:**

   ```bash
   git clone <your-repo-url>
   cd <your-project-folder>
   ```
2. **Configure Environment Files:**

   Update the `.env` and `.env.test` files with your database credentials if needed. The default configuration uses PostgreSQL with:
   
   - Production:  
     `DATABASE_URL="postgresql://symfony:secret@db:5432/symfony?serverVersion=13&charset=utf8"`
     
   - Test:  
     `DATABASE_URL="postgresql://symfony:secret@db:5432/symfony_test?serverVersion=13&charset=utf8"`

3. **Install PHP Dependencies:**

   Run the following inside your Docker container or from your host (if configured correctly):
   
   ```bash
   docker-compose exec app composer install
   ```

4. **Install Node Dependencies (for Artillery, if needed):**
   ```bash
   npm install -g artillery
   ```

## Docker Usage

- **Start Containers:**

  ```bash
  docker-compose up -d --build
  ```
- **Stop Containers:**

  ```bash
  docker-compose down 
  ```
- **Access Application:**

Open your browser at http://localhost:8080.

- **Access Swagger UI:**

Open http://localhost:8080/api/doc in your browser.

## Database Migrations

generate and run migrations

## Load Testing
To test transaction creation, you can use Artillery.

artillery run transaction-load-test.yml

