Stripe local testing guide

1) Install PHP Stripe SDK (if not already):

   composer require stripe/stripe-php

2) Add keys to .env.local (copy .env.local.dist):

   cp .env.local.dist .env.local
   # then edit .env.local and set test keys

3) Install Stripe CLI and login (forward webhooks):

   # https://stripe.com/docs/stripe-cli
   stripe login
   stripe listen --forward-to http://localhost:8000/recharger/webhook/payment-success

4) Run the Symfony dev server and open checkout:

   symfony server:start --no-tls
   # or
   php -S 127.0.0.1:8000 -t public

5) Use Stripe test card numbers when prompted (test publishable key required):

   Card: 4242 4242 4242 4242  Exp: any future date  CVC: any 3 digits

6) Notes about behavior:

- If `STRIPE_WEBHOOK_SECRET` is not set, the client will POST a simulated webhook to the webhook endpoint after a successful payment (development fallback).
- For production-like testing, set `STRIPE_WEBHOOK_SECRET` and use the Stripe CLI to forward events.
- The webhook handler validates signatures when `STRIPE_WEBHOOK_SECRET` is set.

7) Troubleshooting:

- If `client_secret` is empty on the checkout page, ensure `STRIPE_SECRET` is configured and `composer require stripe/stripe-php` was run.
- Check logs in `var/log/` and run `php bin/console cache:clear` after changing env values.
