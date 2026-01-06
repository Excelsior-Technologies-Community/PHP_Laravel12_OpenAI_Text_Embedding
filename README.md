# PHP_Laravel12_OpenAI_Text_Embedding

A complete, production-ready Laravel 12 project demonstrating **OpenAI text embeddings** with automatic **mock mode fallback**, **rate limiting**, and **text similarity comparison**. This project works even **without an OpenAI API key**, making it ideal for demos, learning, and interviews.

---

## Overview

This application converts text into vector embeddings and compares semantic similarity using cosine similarity. It is designed to be stable, safe, and demo-friendly.

If an OpenAI API key is available, the application uses the real OpenAI API. If not, it automatically switches to mock mode and generates deterministic embeddings so that similarity results still behave realistically.

---

## Key Highlights

* Laravel 12 clean architecture
* Service-based OpenAI integration
* Automatic mock mode when API key is missing or invalid
* Local rate limiting (requests per minute)
* Deterministic mock embeddings for consistent results
* Text embedding generation
* Text similarity comparison using cosine similarity
* Token usage tracking
* Clean Tailwind CSS UI
* No JavaScript framework required
* Ideal for learning, demos, and portfolio projects

---

## Use Cases

* Semantic text search
* Document similarity checking
* AI-powered recommendation systems
* Chatbot memory and retrieval
* Academic and MCA final-year projects
* Interview demonstrations without paid API usage

---

## Technology Stack

* PHP 8.1+
* Laravel 12
* OpenAI PHP Client
* Tailwind CSS (CDN)
* Blade Templates
* Laravel Cache

---

## Project Structure

```
app/
├── Services/
│   └── OpenAIService.php
├── Http/
│   └── Controllers/
│       └── EmbeddingController.php

config/
└── services.php

resources/views/
├── layouts/
│   └── app.blade.php
└── embedding/
    ├── index.blade.php
    ├── result.blade.php
    ├── comparison.blade.php
    └── demo.blade.php

routes/
└── web.php
```

---

## Features Explained

### 1. OpenAI Service Layer

All OpenAI logic is isolated inside `OpenAIService.php`.

Responsibilities:

* Generate embeddings
* Handle OpenAI API errors
* Handle rate limiting
* Switch automatically to mock mode
* Calculate cosine similarity
* Track API usage

This keeps controllers clean and reusable.

---

### 2. Mock Mode (No API Key Required)

If `OPENAI_API_KEY` is missing or invalid:

* Application switches to mock mode
* Generates 1536-dimension embeddings
* Embeddings are deterministic (same text → same vector)
* Similar text produces high similarity scores
* Different text produces low similarity scores

This makes the project usable without any paid API.

---

### 3. Rate Limiting

Local rate limiting is implemented using Laravel Cache:

* 60 requests per minute limit
* Automatic wait time calculation
* Friendly error messages
* Manual reset option (local environment)

---

### 4. Text Embedding Generation

Users can:

* Enter any text (up to 5000 characters)
* Generate a vector embedding
* View vector size and token usage
* Inspect sample embedding values

---

### 5. Text Similarity Comparison

Users can:

* Compare two texts
* Get similarity percentage
* View cosine similarity score
* Understand similarity interpretation

Similarity ranges:

* 70–100% : Highly similar
* 30–70%  : Moderately related
* 0–30%   : Unrelated

---

## Installation

Clone the repository:

```
git clone https://github.com/your-username/laravel-openai-embeddings.git
cd laravel-openai-embeddings
```

Install dependencies:

```
composer install
```

Create environment file:

```
cp .env.example .env
```

Generate application key:

```
php artisan key:generate
```

---

## Configuration

### OpenAI API Key (Optional)

Edit `.env` file:

```
OPENAI_API_KEY=your-openai-api-key
```

If you do not provide a key, the app will automatically run in mock mode.

Update `config/services.php`:

```
'openai' => [
    'api_key' => env('OPENAI_API_KEY'),
],
```

---

## Running the Application

Clear caches:

```
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

Start the server:

```
php artisan serve
```

Open in browser:

```
http://localhost:8000
```
---
## Screenshot
<img width="1866" height="971" alt="image" src="https://github.com/user-attachments/assets/c62e46ae-31e1-4884-9b01-4e2c7291c12a" />
<img width="1823" height="908" alt="image" src="https://github.com/user-attachments/assets/174231a4-d32f-494c-92b4-e2c7d964159a" />
<img width="1821" height="970" alt="image" src="https://github.com/user-attachments/assets/2fb14f3e-7f78-4c69-8599-7923760d0d30" />


---

## Routes Overview

| Method | URL                 | Description              |
| ------ | ------------------- | ------------------------ |
| GET    | /                   | Home page                |
| GET    | /demo               | Pre-configured examples  |
| POST   | /embedding/generate | Generate embedding       |
| POST   | /embedding/compare  | Compare text similarity  |
| GET    | /api-status         | API usage status         |
| GET    | /clear-rate-limit   | Reset rate limit (local) |

---

## API Status Monitoring

The UI displays:

* Requests used in the last minute
* Remaining request quota
* Usage percentage
* Mock mode indicator

---

## Error Handling

The system safely handles:

* Invalid API keys
* Rate limit exceeded
* Network failures
* Empty or invalid input

Errors are user-friendly and logged using Laravel logging.

---

## Security Considerations

* Never commit real API keys
* Use environment variables only
* Enable HTTPS in production
* Validate all user input
* Monitor usage limits

---

## Testing

Manual testing is supported via UI.

For automated testing, service logic can be tested independently due to clean separation.

---

## Deployment Notes

For production:

* Use a valid OpenAI API key
* Increase cache storage (Redis recommended)
* Add authentication if exposing publicly
* Add request throttling middleware

---

## Future Enhancements

* Database storage for embeddings
* Vector search using cosine similarity
* File upload embeddings (PDF, DOC)
* Chat-based semantic search
* Admin dashboard

---

## License

This project is open-source and available under the MIT License.

---

## Disclaimer

This project is intended for educational and demonstration purposes. Always follow OpenAI usage policies and best security practices in production environments.
