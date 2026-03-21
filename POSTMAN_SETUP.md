# Postman Collection Setup Guide

## Overview
The updated `class_up_api_postman_collection.json` contains all 70 endpoints of the Class Up API v1, organized into 16 categories with complete request configurations.

## Quick Start

### 1. Import the Collection
- Open Postman
- Click "Import" → Select `class_up_api_postman_collection.json`
- The collection will be imported with all endpoints and configurations

### 2. Configure Environment Variables
Set these variables in your Postman environment:
- `base_url`: `http://localhost` (or your API server)
- `token`: Leave empty initially, fill after login

### 3. Get Authentication Token
1. Run: **🔐 Authentication → Login**
2. Use credentials:
   - email: `john@example.com`
   - password: `password123`
3. Copy the `token` from response
4. Set `{{token}}` variable in your Postman environment

## Endpoint Organization

### 🔐 Authentication (7 endpoints)
- Health Status
- Register
- Login
- Get Current User (Me)
- Forgot Password
- Reset Password
- Logout

### 📋 Public Endpoints (3 endpoints)
- List Plans
- List School Types
- List Modalities

### 💳 Subscriptions (7 endpoints)
- Get Subscription Status
- List Invoices
- Create Checkout Session
- Cancel Subscription
- Resume Subscription
- Subscription Success Redirect
- Subscription Checkout Canceled Redirect

### 👥 Students (5 CRUD endpoints)
- List Students
- Create Student
- Get Student
- Update Student
- Delete Student

### And 10 more categories with CRUD operations...
- 📚 Classes
- 🏫 Classrooms
- 📖 Subjects
- 👤 Guardians
- 📝 Enrollments
- ✋ Attendances
- 🎓 Grades
- 🏆 Certificates
- 🎨 Certificate Templates

### Additional Endpoints
- 📊 Dashboard: Student Growth Analytics
- 💰 Finance: Financial Summary
- 🪝 Stripe Webhook: Handle Stripe Events

## Authentication Details

### Protected Endpoints
All authenticated endpoints include the header:
```
Authorization: Bearer {{token}}
```

### Public Endpoints (No Auth Required)
- POST /api/v1/login
- POST /api/v1/register
- POST /api/v1/forgot-password
- POST /api/v1/reset-password
- GET /api/v1/plans
- GET /api/v1/school-types
- GET /api/v1/modalities
- GET /api/v1/subscription/success
- GET /api/v1/subscription/checkout-canceled
- POST /api/v1/webhooks/stripe

### Subscription-Required Endpoints
These require active subscription validation:
- All CRUD endpoints (Students, Classes, etc.)
- Dashboard endpoints
- Finance endpoints

## Example Workflow

### 1. Register a New User
```
POST /api/v1/register
Body: {
  "name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "admin"
}
```

### 2. Login
```
POST /api/v1/login
Body: {
  "email": "john@example.com",
  "password": "password123"
}
Response: { "token": "..." }
```

### 3. Create a Student
```
POST /api/v1/students
Headers: Authorization: Bearer {{token}}
Body: {
  "name": "Jane",
  "last_name": "Smith",
  "email": "jane@example.com",
  "date_of_birth": "2010-05-15",
  "enrollment_number": "2024001"
}
```

## Request Bodies

Each endpoint includes example request bodies with realistic sample data:
- Student: name, last_name, email, date_of_birth, enrollment_number
- Class: name, grade, teacher_id, shift
- Classroom: name, capacity, location
- Subject: name, code, description
- Guardian: name, last_name, email, phone, relationship
- Enrollment: student_id, class_id, enrollment_date
- Attendance: student_id, class_id, date, status
- Grade: student_id, subject_id, grade, period
- Certificate: student_id, template_id, issue_date, description
- Certificate Template: name, description, content

## Notes

- All timestamps use ISO 8601 format
- IDs in example URLs (1, 2) should be replaced with actual resource IDs
- The collection includes proper HTTP methods (GET, POST, PUT, DELETE)
- All POST/PUT requests include Content-Type: application/json
- Example bodies are realistic and match expected API request formats

## Troubleshooting

### Invalid Token
- Re-run the Login endpoint
- Update {{token}} variable
- Ensure token is not expired

### 401 Unauthorized
- Verify {{token}} is set correctly
- Check if endpoint requires active subscription

### 404 Not Found
- Verify {{base_url}} is correct
- Check if resource ID exists
- Ensure endpoint path is spelled correctly

### Subscription Required
- Some endpoints require active Stripe subscription
- Complete subscription checkout before accessing protected resources
