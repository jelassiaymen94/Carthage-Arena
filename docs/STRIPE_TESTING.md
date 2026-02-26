Stripe local testing guide
==========================

This document explains how to test the Stripe checkout flow locally for the Carthage Arena project.

Prerequisites
-------------
- PHP and Composer installed
- Symfony CLI or ability to run the built-in PHP server
- Stripe CLI (recommended for forwarding webhooks)

Install PHP SDK
---------------
If you haven't yet installed the Stripe PHP SDK run:

```bash
composer require stripe/stripe-php
```

Environment variables
---------------------
Copy the `.env.local.dist` file to `.env.local` and fill in the test keys:

```bash
cp .env.local.dist .env.local
# then edit .env.local and set the test values
```

Set the following values (test keys):

- `STRIPE_SECRET` — test secret key (sk_test_...)
- `STRIPE_PUBLISHABLE` — test publishable key (pk_test_...)
- `STRIPE_WEBHOOK_SECRET` — webhook secret to verify incoming webhooks (optional for dev)

Starting the app
----------------
Run Symfony dev server (or PHP built-in server):

```bash
symfony server:start --no-tls
# or
php -S 127.0.0.1:8000 -t public
```

Local webhook forwarding (recommended)
-------------------------------------
Install the Stripe CLI and forward webhooks to your local webhook endpoint:

```bash
# login once
stripe login

# forward webhooks to your local webhook endpoint
stripe listen --forward-to http://localhost:8000/recharger/webhook/payment-success
```

This lets Stripe send real webhook events to your app so the server-side verification and crediting logic runs as in production.

Testing the checkout flow
-------------------------
1. Open the package checkout page in your browser (e.g. from the Recharger page).
2. The page should receive a `client_secret` from the server and initialize Stripe Elements.
3. Use Stripe test card numbers (example):

   - Card: `4242 4242 4242 4242`
   - Any future expiry date
   - Any CVC (e.g. `123`)

4. Confirm the payment in the browser. If `STRIPE_WEBHOOK_SECRET` is set and Stripe CLI is forwarding events, your webhook handler will receive the `payment_intent.succeeded` event and credit the user CP according to metadata.

Dev fallback (no webhook secret)
--------------------------------
If `STRIPE_WEBHOOK_SECRET` is not configured in `.env.local`, the client contains a development fallback: after a successful `confirmCardPayment` the client will POST a simulated webhook JSON to the same webhook endpoint so the server credits CP locally. This fallback is for convenience only — enable real webhook forwarding for proper testing.

Troubleshooting
---------------
- If the checkout page shows `client_secret` empty: ensure `STRIPE_SECRET` is set and the Stripe PHP SDK is installed, then clear cache:

  ```bash
  php bin/console cache:clear
  ```

- If webhook events are not processed: verify Stripe CLI is running and forwarding, and check the `stripe listen` output for delivery errors.
- Check Symfony logs in `var/log/` for exceptions.

Security notes
--------------
- Use Stripe test keys in development and never commit real secret keys to the repository.
- Always set `STRIPE_WEBHOOK_SECRET` in production and verify signatures with `\\Stripe\\Webhook::constructEvent()` before crediting users.

What to test for your professor
-------------------------------
- The checkout page displays package details and the payment flow completes.
- The server creates a PaymentIntent with metadata containing `user_id` and `cp_amount`.
- The webhook handler verifies the signature and credits the user's balance on `payment_intent.succeeded`.
