# Expense Tracker API Documentation

## Base URL
```
http://localhost:8000/api
```

## Authentication

### Register
**POST** `/register`
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

### Login
**POST** `/login`
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

### Logout
**POST** `/logout`
Headers: `Authorization: Bearer {token}`

### Get Current User
**GET** `/me`
Headers: `Authorization: Bearer {token}`

## Savings API

### Get All Savings
**GET** `/savings`
Headers: `Authorization: Bearer {token}`

### Create Saving
**POST** `/savings`
Headers: `Authorization: Bearer {token}`
```json
{
    "amount": 1000.00,
    "description": "Monthly salary savings",
    "date": "2024-01-15"
}
```

### Get Single Saving
**GET** `/savings/{id}`
Headers: `Authorization: Bearer {token}`

### Update Saving
**PUT** `/savings/{id}`
Headers: `Authorization: Bearer {token}`
```json
{
    "amount": 1200.00,
    "description": "Updated monthly salary savings",
    "date": "2024-01-15"
}
```

### Delete Saving
**DELETE** `/savings/{id}`
Headers: `Authorization: Bearer {token}`

## Expenses API

### Get All Expenses
**GET** `/expenses`
Headers: `Authorization: Bearer {token}`

Query Parameters:
- `category` - Filter by category
- `start_date` - Filter expenses from this date
- `end_date` - Filter expenses until this date

### Create Expense
**POST** `/expenses`
Headers: `Authorization: Bearer {token}`
```json
{
    "amount": 50.00,
    "description": "Grocery shopping",
    "category": "Food",
    "date": "2024-01-15"
}
```

### Get Single Expense
**GET** `/expenses/{id}`
Headers: `Authorization: Bearer {token}`

### Update Expense
**PUT** `/expenses/{id}`
Headers: `Authorization: Bearer {token}`
```json
{
    "amount": 75.00,
    "description": "Updated grocery shopping",
    "category": "Food",
    "date": "2024-01-15"
}
```

### Delete Expense
**DELETE** `/expenses/{id}`
Headers: `Authorization: Bearer {token}`

## Analytics API

### Monthly Analysis
**GET** `/analytics/monthly?month=2024-01`
Headers: `Authorization: Bearer {token}`

Returns:
- Total expenses for the month
- Category breakdown
- Comparison with previous month
- Percentage change

### Yearly Analysis
**GET** `/analytics/yearly?year=2024`
Headers: `Authorization: Bearer {token}`

Returns:
- Monthly breakdown for the year
- Category breakdown for the year
- Total yearly expenses

### Categories Summary
**GET** `/analytics/categories`
Headers: `Authorization: Bearer {token}`

Returns:
- All expense categories with totals, counts, and averages

### Savings vs Expenses
**GET** `/analytics/savings-vs-expenses?start_date=2024-01-01&end_date=2024-01-31`
Headers: `Authorization: Bearer {token}`

Returns:
- Total savings and expenses for the period
- Net amount (savings - expenses)
- Savings rate percentage

### Total Savings
**GET** `/analytics/total-savings`
Headers: `Authorization: Bearer {token}`

Returns:
- Total amount of all savings
- Count of savings entries
- Average saving amount
- Recent savings (last 5)

### Total Monthly Expenses
**GET** `/analytics/total-monthly-expenses?month=2024-01`
Headers: `Authorization: Bearer {token}`

Returns:
- Total expenses for the specified month
- Count of expenses for the month
- Average expense amount
- Top expense categories for the month

### Total Yearly Expenses
**GET** `/analytics/total-yearly-expenses?year=2024`
Headers: `Authorization: Bearer {token}`

Returns:
- Total expenses for the specified year
- Count of expenses for the year
- Average expense amount
- Average monthly expense
- Monthly totals breakdown
- Top expense categories for the year

## Response Format

### Success Response
```json
{
    "message": "Success message",
    "data": {
        // Response data
    }
}
```

### Error Response
```json
{
    "message": "Error message",
    "errors": {
        "field": ["Validation error message"]
    }
}
```

## Status Codes
- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 422: Validation Error
- 500: Internal Server Error