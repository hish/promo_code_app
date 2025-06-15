# 🎟️ Promo Code App

## 📋 Description

A simple Laravel application for creating and redeeming promo codes.

It exposes two main API endpoints:

- `POST /api/promo-codes/create` — Create a promo code  
- `POST /api/promo-codes/redeem` — Redeem a promo code

---

## ⚙️ Installation & Setup

1. **Clone the repository and navigate into the project directory:**

```bash
   git clone git@github.com:hish/promo_code_app.git
   cd promo-code-app
```
2. **Copy the example environment file and keep its contents unchanged:**
```bash
    cp .env.example .env
```
3. **Build and start the Docker containers:**
```bash
docker compose up --build
```

## 🐳 Docker Services
Running the above command starts the following containers:

 - promo_code_app — Laravel application (PHP 8+)

 - promo_code_db — SQLite or MySQL database container

 - promo_code_adminer — Adminer UI for managing the database (accessible at http://localhost:8080)

## 📫 Postman Collection ##
A ready-to-use Postman collection is included in the repository to help you test the API easily.
```bash
    Promo_Code.postman_collection.json
```


 ## 🧪 Running Tests
To run the unit tests inside the app container:
 ```bash
 docker exec -it promo_code_app php artisan test
 ```