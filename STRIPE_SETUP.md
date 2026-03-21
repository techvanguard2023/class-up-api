# 💳 Stripe Integration Setup Guide

## Overview
This document explains how to set up Stripe payment processing for Class Up API subscriptions.

## 1. Installation

### 1.1 Install Stripe PHP SDK
```bash
composer install
```

The `stripe/stripe-php` package is already included in `composer.json`.

### 1.2 Run Migrations
```bash
# Run all migrations including subscription-related tables
php artisan migrate

# Or run specific migrations
php artisan migrate --path=database/migrations/2026_03_19_000001_add_stripe_fields_to_subscriptions_table.php
php artisan migrate --path=database/migrations/2026_03_19_000002_create_feature_usage_table.php
php artisan migrate --path=database/migrations/2026_03_19_000003_add_stripe_price_id_to_plans_table.php
```

### 1.3 Seed Plans and Features
```bash
php artisan db:seed --class=PlanAndFeatureSeeder
```

This creates 4 default plans:
- **Free**: 10 students max
- **Bronze**: 100 students max
- **Silver**: 500 students max
- **Gold**: Unlimited students

## 2. Stripe Account Setup

### 2.1 Create a Stripe Account
1. Go to https://stripe.com
2. Create a free developer account
3. Navigate to Dashboard → Developers → API keys

### 2.2 Get Your API Keys
You'll need two keys:
- **Publishable Key** (starts with `pk_`)
- **Secret Key** (starts with `sk_`)

### 2.3 Create Products and Prices in Stripe

**Important**: You must create products and price IDs in Stripe Dashboard for each plan.

#### Steps:
1. Go to Stripe Dashboard → Products
2. Create a new product for each plan:

**Example for Bronze Plan:**
- Name: `Bronze Plan`
- Description: `For small schools`
- Pricing type: `Recurring`
- Billing period: `Monthly`
- Price: `$29.90`
- Currency: `USD`

3. After creating, copy the **Price ID** (starts with `price_`)

#### Store Price IDs
Update each plan with its Stripe Price ID:

```bash
php artisan tinker

# Update plans with Stripe price IDs
$bronze = Plan::where('name', 'Bronze')->first();
$bronze->update(['stripe_price_id' => 'price_YOUR_BRONZE_PRICE_ID']);

$silver = Plan::where('name', 'Silver')->first();
$silver->update(['stripe_price_id' => 'price_YOUR_SILVER_PRICE_ID']);

$gold = Plan::where('name', 'Gold')->first();
$gold->update(['stripe_price_id' => 'price_YOUR_GOLD_PRICE_ID']);
```

## 3. Environment Configuration

### 3.1 Update .env File
```env
STRIPE_KEY=pk_test_YOUR_PUBLISHABLE_KEY
STRIPE_SECRET=sk_test_YOUR_SECRET_KEY
STRIPE_WEBHOOK_SECRET=whsec_YOUR_WEBHOOK_SECRET
```

Replace with your actual keys from Stripe Dashboard.

### 3.2 Test Keys vs Live Keys
- **Development**: Use keys starting with `pk_test_` and `sk_test_`
- **Production**: Use keys starting with `pk_live_` and `sk_live_`

## 4. Webhook Configuration

### 4.1 Create Webhook Endpoint
1. Go to Stripe Dashboard → Developers → Webhooks
2. Click "Add endpoint"
3. Endpoint URL: `https://yourdomain.com/api/v1/webhooks/stripe`
4. Select events:
   - `customer.subscription.created`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`
   - `invoice.payment_succeeded`
   - `invoice.payment_failed`

### 4.2 Get Webhook Secret
After creating the webhook endpoint, copy the signing secret:
- Add to `.env`: `STRIPE_WEBHOOK_SECRET=whsec_...`

### 4.3 Test Webhook Locally
Use Stripe CLI to forward webhooks to local machine:

```bash
# Install Stripe CLI from https://stripe.com/docs/stripe-cli
stripe listen --forward-to localhost:8000/api/v1/webhooks/stripe
```

This will display your signing secret - update `.env` with it.

## 5. API Endpoints

### 5.1 Public Endpoints

#### Get Available Plans
```
GET /api/v1/plans
```

Response:
```json
{
  "data": [
    {
      "id": 1,
      "name": "Bronze",
      "price": 29.90,
      "billing_cycle": "monthly",
      "features": [
        {
          "id": 1,
          "name": "students_management",
          "limit": 100
        }
      ]
    }
  ]
}
```

### 5.2 Protected Endpoints (Require Authentication)

#### Start Checkout
```
POST /api/v1/subscription/checkout
Authorization: Bearer {token}
Content-Type: application/json

{
  "plan_id": 2
}
```

Response:
```json
{
  "checkout_url": "https://checkout.stripe.com/...",
  "session_id": "cs_..."
}
```

#### Get Subscription Status
```
GET /api/v1/subscription/status
Authorization: Bearer {token}
```

Response:
```json
{
  "active": true,
  "status": "active",
  "is_trial": false,
  "plan": {
    "id": 2,
    "name": "Bronze",
    "price": 29.90
  },
  "features": ["students_management", "classrooms_management"],
  "expires_at": "2026-04-19T10:00:00Z",
  "cancel_at_period_end": false
}
```

#### Cancel Subscription
```
POST /api/v1/subscription/cancel
Authorization: Bearer {token}
```

Response:
```json
{
  "message": "Subscription will be canceled at the end of the billing period",
  "expires_at": "2026-04-19T10:00:00Z"
}
```

#### Resume Subscription
```
POST /api/v1/subscription/resume
Authorization: Bearer {token}
```

### 5.3 Protected Routes (Require Active Subscription)

All existing endpoints are protected:
```
POST /api/v1/students
GET /api/v1/students
GET /api/v1/students/{id}
PUT /api/v1/students/{id}
DELETE /api/v1/students/{id}

POST /api/v1/classrooms
GET /api/v1/classrooms
... and more
```

If user doesn't have active subscription, response:
```json
{
  "error": "No active subscription",
  "code": "SUBSCRIPTION_INACTIVE",
  "message": "Your subscription is inactive or expired. Please upgrade your plan."
}
```

### 5.4 Feature-Specific Routes (Future)

Routes can require specific features:
```php
Route::middleware('validate.subscription:reports_advanced')->group(function () {
    Route::get('reports/advanced', [ReportController::class, 'advanced']);
});
```

If user's plan doesn't include feature:
```json
{
  "error": "Feature 'reports_advanced' not available",
  "code": "FEATURE_UNAVAILABLE"
}
```

## 6. Testing

### 6.1 Test with Stripe Test Cards
Use Stripe test card numbers:

| Card Type | Number | CVC | Expiry |
|-----------|--------|-----|--------|
| Visa | 4242 4242 4242 4242 | Any | Any future date |
| Visa Decline | 4000 0000 0000 0002 | Any | Any future date |
| 3D Secure | 4000 0025 0000 3155 | Any | Any future date |

### 6.2 Test Payment Flow

1. **Register a user**
   ```bash
   POST /api/v1/register
   {
     "name": "John Doe",
     "email": "john@example.com",
     "password": "password123"
   }
   ```

2. **Get plans**
   ```bash
   GET /api/v1/plans
   ```

3. **Create checkout session**
   ```bash
   POST /api/v1/subscription/checkout
   Authorization: Bearer {token}
   {
     "plan_id": 2
   }
   ```

4. **Complete payment** in Stripe Checkout

5. **Check subscription status**
   ```bash
   GET /api/v1/subscription/status
   Authorization: Bearer {token}
   ```

6. **Access protected routes**
   ```bash
   GET /api/v1/students
   Authorization: Bearer {token}
   ```

## 7. Feature Usage Limits

Plans have limits on features. When limit is exceeded:

```json
{
  "error": "Feature 'students_management' limit exceeded",
  "code": "FEATURE_LIMIT_EXCEEDED",
  "message": "You have reached the limit for this feature this month.",
  "available": 0
}
```

## 8. Database Schema

### Subscriptions Table
```sql
id (bigint)
school_id (bigint) - foreign key
user_id (bigint) - foreign key
plan_id (bigint) - foreign key
status (enum) - active, canceled, expired, trial, past_due
starts_at (timestamp)
ends_at (timestamp)
trial_ends_at (timestamp)
payment_method (string)
external_id (string)
stripe_customer_id (string)
stripe_subscription_id (string)
stripe_price_id (string)
cancel_at_period_end (boolean)
```

### Feature Usage Table
```sql
id (bigint)
subscription_id (bigint) - foreign key
feature_id (bigint) - foreign key
count (integer) - usage count this month
month (string) - YYYY-MM format
```

## 9. Common Tasks

### List All Subscriptions
```bash
php artisan tinker
Subscription::with('user', 'plan')->get();
```

### Update a Plan's Price
```bash
php artisan tinker
$plan = Plan::find(1);
$plan->update(['price' => 39.90]);
```

### Check User's Features
```bash
php artisan tinker
$user = User::find(1);
$user->subscription->plan->features;
```

### Manually Create Subscription (for testing)
```bash
php artisan tinker
Subscription::create([
    'user_id' => 1,
    'school_id' => 1,
    'plan_id' => 2,
    'status' => 'active',
    'starts_at' => now(),
    'ends_at' => now()->addMonth(),
    'payment_method' => 'stripe',
]);
```

## 10. Troubleshooting

### "No active subscription" error
- Ensure user has a subscription record in database
- Check subscription `status` is `active`
- Check `ends_at` is in the future

### Webhook not triggering
- Verify endpoint URL is correct
- Check webhook signing secret matches `STRIPE_WEBHOOK_SECRET`
- Use Stripe CLI to test locally: `stripe listen --forward-to localhost:8000/...`
- Check Laravel logs: `tail -f storage/logs/laravel.log`

### "stripe_price_id is null" error
- Make sure plans have Stripe Price IDs set
- Update plan: `$plan->update(['stripe_price_id' => 'price_...'])`

### Payment failed
- Check Stripe Dashboard for declined cards
- Verify test card is valid (see section 6.1)
- Check webhook configuration

## 11. Going to Production

1. **Switch to Live Keys**
   ```env
   STRIPE_KEY=pk_live_YOUR_LIVE_KEY
   STRIPE_SECRET=sk_live_YOUR_LIVE_SECRET
   ```

2. **Update Products in Stripe**
   - Replace all test Price IDs with live Price IDs
   - Update plans: `$plan->update(['stripe_price_id' => 'price_...'])`

3. **Update Webhook URL**
   - Point to production domain
   - Use production webhook secret

4. **HTTPS Required**
   - Ensure production domain uses HTTPS
   - Stripe requires HTTPS for all interactions

5. **Test Again**
   - Use real credit card (charged $0-$1, refunded)
   - Verify subscriptions are created
   - Check webhook events are received

## Resources

- [Stripe Dashboard](https://dashboard.stripe.com)
- [Stripe API Documentation](https://stripe.com/docs/api)
- [Stripe PHP Library](https://github.com/stripe/stripe-php)
- [Stripe Test Cards](https://stripe.com/docs/testing)
