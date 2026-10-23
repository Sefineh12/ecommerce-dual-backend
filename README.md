# Dual-Backend E-commerce Microservice Architecture

A lightweight dual-backend e-commerce REST API system built and deployed on Android using Termux.

## 🏗️ Architecture Overview

The system utilizes a microservice-style separation between product CRUD operations and business analytics:

- **PHP REST API (Port 8000):** Handles core e-commerce features such as product listings using PHP (PDO) connected to MariaDB.
- **Python Flask Microservice (Port 8001):** Handles lightweight analytics, including sales summaries and product recommendation endpoints.
- **MariaDB Database (`ecommerce_db`):** Central database shared by both services.

## 🚀 Getting Started

### Prerequisites
- Termux on Android
- PHP, Python 3, MariaDB

### Running the Services

1. **Start MariaDB & PHP API:**
   ```bash
   mysql -u root
   php -S 0.0.0.0:8000 -t /sdcard/Download/ECOMMERCE-BACKEND/api
