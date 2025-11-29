# Test API Endpoints

## 1. Test Registration
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

## 2. Test Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

## 3. Test Creating a Saving (replace {TOKEN} with actual token from login)
```bash
curl -X POST http://localhost:8000/api/savings \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {TOKEN}" \
  -d '{
    "amount": 1000.00,
    "description": "Monthly salary savings",
    "date": "2024-01-15"
  }'
```

## 4. Test Creating an Expense (replace {TOKEN} with actual token from login)
```bash
curl -X POST http://localhost:8000/api/expenses \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {TOKEN}" \
  -d '{
    "amount": 50.00,
    "description": "Grocery shopping",
    "category": "Food",
    "date": "2024-01-15"
  }'
```

## 5. Test Monthly Analysis (replace {TOKEN} with actual token from login)
```bash
curl -X GET "http://localhost:8000/api/analytics/monthly?month=2024-01" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {TOKEN}"
```

## 6. Test Get All Savings (replace {TOKEN} with actual token from login)
```bash
curl -X GET http://localhost:8000/api/savings \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {TOKEN}"
```

## 7. Test Get All Expenses (replace {TOKEN} with actual token from login)
```bash
curl -X GET http://localhost:8000/api/expenses \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {TOKEN}"
```