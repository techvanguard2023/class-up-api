# 🚀 API Usage Examples

## Authentication

### Register
```bash
curl -X POST http://localhost/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "João",
    "last_name": "Silva",
    "email": "joao@school.com",
    "password": "senha123",
    "password_confirmation": "senha123"
  }'
```

### Login
```bash
curl -X POST http://localhost/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "joao@school.com",
    "password": "senha123"
  }'
```

Response:
```json
{
  "user": {
    "id": 1,
    "name": "João",
    "email": "joao@school.com"
  },
  "token": "1|abc123def456..."
}
```

**Store the token for authenticated requests**

---

## Subscription Management

### 1. Get Available Plans
```bash
curl -X GET http://localhost/api/v1/plans
```

Response:
```json
{
  "data": [
    {
      "id": 1,
      "name": "Free",
      "description": "Ideal para começar",
      "price": "0.00",
      "billing_cycle": "monthly",
      "color": "gray",
      "features": [
        {
          "id": 1,
          "name": "students_management",
          "limit": 10
        },
        {
          "id": 2,
          "name": "classrooms_management",
          "limit": 1
        }
      ]
    },
    {
      "id": 2,
      "name": "Bronze",
      "description": "Para pequenas escolas",
      "price": "29.90",
      "billing_cycle": "monthly",
      "color": "amber",
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

### 2. Create Checkout Session
```bash
curl -X POST http://localhost/api/v1/subscription/checkout \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "plan_id": 2
  }'
```

Response:
```json
{
  "checkout_url": "https://checkout.stripe.com/pay/cs_...",
  "session_id": "cs_test_..."
}
```

**Redirect user to `checkout_url` in frontend to complete payment**

### 3. Check Subscription Status
```bash
curl -X GET http://localhost/api/v1/subscription/status \
  -H "Authorization: Bearer YOUR_TOKEN"
```

Response (No subscription):
```json
{
  "active": false,
  "message": "No active subscription"
}
```

Response (Active subscription):
```json
{
  "active": true,
  "status": "active",
  "is_trial": false,
  "is_expired": false,
  "plan": {
    "id": 2,
    "name": "Bronze",
    "description": "Para pequenas escolas",
    "price": "29.90",
    "billing_cycle": "monthly"
  },
  "features": [
    "students_management",
    "classrooms_management",
    "grades_management"
  ],
  "started_at": "2026-03-19T10:30:00Z",
  "expires_at": "2026-04-19T10:30:00Z",
  "trial_ends_at": null,
  "cancel_at_period_end": false,
  "payment_method": "stripe"
}
```

### 4. Cancel Subscription
```bash
curl -X POST http://localhost/api/v1/subscription/cancel \
  -H "Authorization: Bearer YOUR_TOKEN"
```

Response:
```json
{
  "message": "Subscription will be canceled at the end of the billing period",
  "expires_at": "2026-04-19T10:30:00Z"
}
```

**Note**: Cancels at end of billing period, not immediately

### 5. Resume Subscription
```bash
curl -X POST http://localhost/api/v1/subscription/resume \
  -H "Authorization: Bearer YOUR_TOKEN"
```

Response:
```json
{
  "message": "Subscription resumption scheduled",
  "expires_at": "2026-04-19T10:30:00Z"
}
```

---

## Protected Routes (Require Active Subscription)

Once user has active subscription, they can access main features:

### Create Student
```bash
curl -X POST http://localhost/api/v1/students \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Maria",
    "email": "maria@student.com",
    "birth_date": "2010-05-15"
  }'
```

**Without subscription or if limit exceeded:**
```json
{
  "error": "No active subscription",
  "code": "SUBSCRIPTION_INACTIVE",
  "message": "Your subscription is inactive or expired. Please upgrade your plan."
}
```

### Get Students
```bash
curl -X GET http://localhost/api/v1/students \
  -H "Authorization: Bearer YOUR_TOKEN"
```

Response:
```json
{
  "data": [
    {
      "id": 1,
      "name": "Maria",
      "email": "maria@student.com",
      "birth_date": "2010-05-15",
      "school_id": 1,
      "created_at": "2026-03-19T10:30:00Z"
    }
  ]
}
```

### Create Classroom
```bash
curl -X POST http://localhost/api/v1/classrooms \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "7º Ano A",
    "year": 2026,
    "level": "junior"
  }'
```

### Get Classrooms
```bash
curl -X GET http://localhost/api/v1/classrooms \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Create Subject
```bash
curl -X POST http://localhost/api/v1/subjects \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Mathematics",
    "code": "MAT101"
  }'
```

### Get Subjects
```bash
curl -X GET http://localhost/api/v1/subjects \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Create Enrollment
```bash
curl -X POST http://localhost/api/v1/enrollments \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": 1,
    "classroom_id": 1,
    "enrollment_date": "2026-03-19"
  }'
```

### Register Attendance
```bash
curl -X POST http://localhost/api/v1/attendances \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": 1,
    "enrollment_id": 1,
    "attendance_date": "2026-03-19",
    "present": true
  }'
```

### Record Grade
```bash
curl -X POST http://localhost/api/v1/grades \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": 1,
    "enrollment_id": 1,
    "subject_id": 1,
    "grade": 8.5,
    "grade_type": "midterm",
    "grading_date": "2026-03-19"
  }'
```

---

## Error Responses

### Missing subscription
```json
{
  "error": "No active subscription",
  "code": "SUBSCRIPTION_INACTIVE",
  "message": "Your subscription is inactive or expired. Please upgrade your plan."
}
```

### Feature not available in plan
```json
{
  "error": "Feature 'reports_advanced' not available",
  "code": "FEATURE_UNAVAILABLE",
  "message": "This feature is not included in your current plan. Please upgrade to access it.",
  "feature": "reports_advanced"
}
```

### Feature limit exceeded
```json
{
  "error": "Feature 'students_management' limit exceeded",
  "code": "FEATURE_LIMIT_EXCEEDED",
  "message": "You have reached the limit for this feature this month.",
  "feature": "students_management",
  "available": 0
}
```

### Unauthorized
```json
{
  "error": "Unauthorized",
  "code": "UNAUTHENTICATED"
}
```

### Validation error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": ["The name field is required."]
  }
}
```

---

## Flow Diagram

```
1. User Registers
   POST /api/v1/register
         ↓
2. User Logs In
   POST /api/v1/login
   Returns: token
         ↓
3. User Gets Plans
   GET /api/v1/plans
         ↓
4. User Creates Checkout
   POST /api/v1/subscription/checkout
   Returns: checkout_url
         ↓
5. User Completes Payment
   (Redirected to Stripe)
         ↓
6. Stripe Webhook Triggers
   POST /webhooks/stripe
   (Backend creates subscription)
         ↓
7. User Can Use Features
   GET /api/v1/subscription/status (status: active)
   POST /api/v1/students
   POST /api/v1/classrooms
   etc...
```

---

## Tips

✅ **Always include token in protected routes**
```bash
-H "Authorization: Bearer YOUR_TOKEN"
```

✅ **Free plan allows 10 students**
- No payment required
- Automatic subscription

✅ **Check subscription before showing UI**
```bash
GET /api/v1/subscription/status
```

✅ **Trial period available**
- Set via admin
- Different status: `is_trial: true`

✅ **Handle limit exceeded gracefully**
- Check response code `FEATURE_LIMIT_EXCEEDED`
- Show upgrade prompt to user
- Try again next month OR upgrade plan

---

## Testing Workflow

1. **Register test user**
   ```bash
   email: test@example.com
   password: password123
   ```

2. **Login and get token**
   - Store token for subsequent requests

3. **Check plans**
   - Use Free plan for testing

4. **Try to create student**
   - Should work (Free plan allows 10)

5. **Check status**
   - Should show active subscription (Free plan)

6. **Upgrade to paid plan**
   - Use Stripe test card: `4242 4242 4242 4242`
   - CVC: any number
   - Expiry: any future date

7. **Verify subscription updated**
   - Status shows paid plan instead of Free
