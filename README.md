# Shoebox — PHP + MySQL (XAMPP)

Kerala premium sneaker e-commerce with Razorpay UPI integration.

## Setup

1. Start **Apache** and **MySQL** in XAMPP.
2. Import `database/schema.sql` in phpMyAdmin.
3. Copy `.env.example` → `.env` and fill in values.
4. Open `http://localhost/shoebox2/`

**Demo login:** `shadhamol2020@gmail.com` / `password`

## Razorpay configuration

Add keys from [Razorpay Dashboard](https://dashboard.razorpay.com/app/keys) to `.env`:

```env
RAZORPAY_KEY_ID=rzp_test_xxxxxxxx
RAZORPAY_KEY_SECRET=your_secret_here
RAZORPAY_MOCK_MODE=false
```

For local testing without real keys, keep `RAZORPAY_MOCK_MODE=true` and use the sandbox confirm button at checkout.

## Production deployment

Set these in `.env` before going live:

```env
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE=true
APP_URL=https://yourdomain.com
```

Also configure:
- HTTPS on Apache (required for `SESSION_SECURE=true`)
- Razorpay live keys (`rzp_live_*`) and webhook secret
- Webhook URL: `https://yourdomain.com/shoebox2/api/razorpay-webhook.php`

Run `database/migration_unique_razorpay_payment.sql` if upgrading an existing database.

## Backend API

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `api/auth.php` | POST | Login, register, profile, logout |
| `api/cart.php` | POST | Add, update, remove cart items |
| `api/checkout.php` | POST | Checkout steps (delivery → payment → COD) |
| `api/razorpay-order.php` | POST | Create Razorpay order (JSON) |
| `api/razorpay-verify.php` | POST | Verify UPI payment & create order (JSON) |
| `api/razorpay-webhook.php` | POST | Razorpay async webhook |
| `api/wishlist.php` | POST | Toggle wishlist |
| `api/compare.php` | POST | Toggle compare list |
| `api/contact.php` | POST | Contact form |
| `api/newsletter.php` | POST | Newsletter signup |

All POST endpoints require CSRF tokens. JSON endpoints accept `csrf_token` in the request body.

## Security

- All secrets in `.env` only (never commit this file).
- Web access blocked to `.env`, `config/`, `database/`, `includes/`, and `storage/` via `.htaccess`.
- CSRF tokens on all POST forms and API calls.
- UPI payments verified with HMAC signature before orders are created.
- Cart prices re-synced from MySQL at checkout (session tampering prevented).
- Stock validated and decremented on order placement.
- Rate limiting on auth, contact, newsletter, and payment verification.
- Order tracking requires email verification for guests.
- Secure session cookies (HttpOnly, SameSite=Lax, Secure in production).
- Security headers: X-Frame-Options, X-Content-Type-Options, Referrer-Policy.
