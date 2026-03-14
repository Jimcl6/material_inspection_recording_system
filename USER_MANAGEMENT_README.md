# User Management Module with QR Code Support

## Overview
This module adds comprehensive user management capabilities to the Material Inspection Recording System, including QR code generation and scanning for employee identification.

## Features
- User CRUD operations with role-based access control
- Department and position management
- QR code generation for employees
- QR code scanner for quick identification
- Employment status tracking (Regular, Contractual, Probationary)
- Login history tracking
- Bulk user operations
- Advanced filtering and search

## Installation Instructions

### 1. Run Database Migrations
```bash
php artisan migrate
```

### 2. Seed Initial Data
```bash
php artisan db:seed --class=UserManagementSeeder
```

### 3. Install Frontend Dependencies
```bash
npm install
```

### 4. Build Assets
```bash
npm run build
```

### 5. Clear Configuration Cache
```bash
php artisan config:clear
php artisan route:clear
```

## Usage Instructions

### Creating Users
1. Navigate to User Management (visible to admin users only)
2. Click "Add User"
3. Fill in user details:
   - Basic information (name, email, employee ID)
   - Password
   - Role and position assignment
   - Employment details
   - Check "Generate QR Code" to create a QR code for the user

### Scanning QR Codes
1. Navigate to User Management → QR Scanner
2. Allow camera access when prompted
3. Scan employee QR code
4. System will identify the user and record the scan
5. Manual input also available if camera is not accessible

### Managing Users
- View all users in the index page
- Filter by role, department, status, or employment type
- Edit user details
- Deactivate users (soft delete)
- Bulk operations available

## QR Code Data Format
QR codes contain encrypted JSON data:
```json
{
    "id": 1,
    "employee_id": "EMP001",
    "name": "John Doe",
    "email": "john@example.com",
    "status": "regular",
    "generated_at": "2026-03-11T10:00:00Z",
    "signature": "hmac_signature"
}
```

## Security Features
- QR data is encrypted using Laravel's encryption
- HMAC signature verification prevents tampering
- Only active users can be authenticated via QR code
- All scans are logged with IP and user agent

## API Endpoints

### User Management
- `GET /users` - List users
- `POST /users` - Create user
- `GET /users/{id}` - Show user
- `PUT /users/{id}` - Update user
- `DELETE /users/{id}` - Deactivate user
- `POST /users/bulk-action` - Bulk operations

### QR Code Operations
- `GET /users/scanner` - Scanner page
- `POST /users/scan` - Process QR scan

## Database Tables

### New Tables
- `departments` - Company departments
- `positions` - Job positions
- `user_qr_codes` - QR code data for users
- `user_permissions` - Permission definitions
- `role_permissions` - Role-permission mapping
- `user_login_history` - Login audit trail

### Updated Tables
- `users` - Added new fields for employee details
- `roles` - Added permission relationships

## Permissions System

### Modules
- users: create, read, update, delete, scan
- annealing: create, read, update, delete, verify
- material: create, read, update, delete
- temperature: create, read, update, delete
- torque: create, read, update, delete
- production: create, read, update, delete
- reports: read, export
- dashboard: read

## Integration with Existing Modules

### Annealing Checks
- Users can be selected as PIC, Inspector, or Verifier
- QR code scanning can auto-fill these fields

### Material Monitoring
- Operators can be assigned via QR scan
- Audit trail includes user information

## Troubleshooting

### QR Scanner Not Working
1. Check camera permissions in browser
2. Ensure using HTTPS or localhost
3. Try manual input as fallback

### Users Not Visible
1. Check if user has admin role
2. Verify navigation permissions
3. Clear browser cache

### Migration Errors
1. Ensure all dependencies are installed
2. Check database connection
3. Run `php artisan migrate:fresh` if needed (WARNING: This will delete all data)

## Future Enhancements

1. Mobile app for QR scanning
2. Badge printing integration
3. Advanced reporting and analytics
4. Two-factor authentication with QR
5. Integration with attendance systems
