# Role-Based Access Control (RBAC) - Implementation Guide

## Overview
Sistem ini mengimplementasikan Role-Based Access Control (RBAC) dengan dua role:
- **Admin**: Akses penuh ke semua fitur
- **Karyawan**: Akses terbatas ke fitur operasional

## Setup Instructions

### 1. Database Changes
Jalankan SQL script untuk menambah kolom `role` ke table `data_user`:

```bash
# Option A: Langsung di MySQL:
1. Buka phpMyAdmin
2. Pilih database 'skripsi_daniel'
3. Pilih table 'data_user'
4. Click "SQL" tab
5. Copy-paste isi dari sql/add_roles.sql
6. Click "Go" untuk execute

# Option B: Via command line:
mysql -u root -p skripsi_daniel < sql/add_roles.sql
```

### 2. Verify Database Changes
```sql
SELECT usernameNo, username, role FROM data_user;
-- Pastikan kolom 'role' sudah ada dan berisi values 'admin' atau 'karyawan'
```

## Role Access Structure

### Admin Role
Admin memiliki akses ke **SEMUA** module:
- ✅ Dashboard
- ✅ Menu (Menu + Varian Menu)
- ✅ Resep (Resep + Detail Resep + Satuan)
- ✅ Stok (Stok + Bahan + Produsen)
- ✅ Transaksi (Transaksi + Detail Transaksi)
- ✅ Voucher
- ✅ Metode Pembayaran
- ✅ User Management
- ✅ Karyawan Management

### Karyawan Role
Karyawan hanya bisa akses module operasional:
- ✅ Dashboard (read-only)
- ✅ Menu (Menu + Varian Menu)
- ✅ Resep (Resep + Detail Resep + Satuan)
- ✅ Stok (Stok + Bahan)
- ❌ Produsen (hanya admin)
- ✅ Transaksi (Transaksi + Detail Transaksi)
- ✅ Voucher
- ✅ Metode Pembayaran
- ❌ User Management
- ❌ Karyawan Management

## Implementation Details

### Modified Files

#### 1. helpers/auth_helper.php
**Purpose**: Central RBAC utility functions
```php
// Check if user is logged in
isLoggedIn()

// Get current user's role
getUserRole()

// Check if user has specific role
hasRole('admin')
hasAnyRole(['admin', 'karyawan'])

// Get allowed modules for current role
getAllowedModules()

// Check module access
canAccessModule('menu')

// Require login (redirect if not logged in)
requireLogin()

// Require specific role (404 if wrong role)
requireRole('admin')
```

#### 2. auth/process_login.php
**Changes**:
- SELECT query now includes `role` column
- Session stores `role` value: `$_SESSION['role']`
- Default role: 'karyawan' if not set in DB

#### 3. components/sidebar.php
**Changes**:
- Include auth_helper.php untuk access control functions
- Filter menu items based on user role
- Display username + role di sidebar header
- Add logout button dengan styling

#### 4. config/session.php
**No changes needed** - Sidebar handling login validation

### Usage in Pages

#### Protect Admin-Only Pages
Add ini di top of page setelah database include:
```php
<?php
session_start();
require_once __DIR__ . '/../helpers/auth_helper.php';

// Check login
requireLogin();

// For admin-only pages:
requireRole('admin');

// OR allow multiple roles:
requireRole(['admin', 'karyawan']);
?>
```

#### Check Role in Template
```php
<?php if (hasRole('admin')): ?>
    <button>Edit</button>
<?php endif; ?>

<?php if (hasAnyRole(['admin', 'karyawan'])): ?>
    <p>Operational info</p>
<?php endif; ?>
```

## Testing RBAC

### Test 1: Login as Admin
1. Update database: SET usernameNo=1 to role='admin'
2. Login dengan admin user
3. Verify sidebar menampilkan SEMUA menu items
4. Verify header menampilkan "ADMIN"

### Test 2: Login as Karyawan
1. Create new user atau update existing user: SET role='karyawan'
2. Login dengan karyawan user
3. Verify sidebar hanya menampilkan allowed modules
4. Try akses admin-only page (harus redirect ke dashboard)

### Test 3: Redirect to Login
1. Logout
2. Try akses protected page langsung via URL
3. Harus redirect ke login page

## Module Protection Status

### Status: RBAC Filtering Ready
- ✅ Sidebar filtering implemented
- ✅ Login system updated to track role
- ✅ Auth helper functions created
- ✅ Logout functionality available
- ⏳ Admin-only pages need explicit requireRole() calls (optional)

### Recommended Next Steps (Optional)
Untuk additional security, tambahkan `requireRole('admin')` di:
- modules/users/* pages
- modules/karyawan/* pages
- modules/produsen/* pages

Example:
```php
<?php
session_start();
require_once __DIR__ . '/../../helpers/auth_helper.php';
requireRole('admin'); // Ini page hanya untuk admin
?>
```

## Session Variables Available

After login, tersedia session variables:
```php
$_SESSION['login']      // true jika user logged in
$_SESSION['username']   // username string
$_SESSION['usernameNo'] // user ID dari database
$_SESSION['role']       // 'admin' atau 'karyawan'
```

## Troubleshooting

### Problem: Sidebar tidak menampilkan menu items
**Solution**: 
- Check apakah session dimulai dengan `session_start()`
- Verify data_user table punya role column dengan values
- Check error log untuk error messages

### Problem: Logout button tidak work
**Solution**:
- Verify auth/logout.php exists
- Check browser console untuk error messages
- Clear browser cache

### Problem: User masih bisa akses protected page
**Solution**:
- Add `requireRole('admin')` di top of protected pages
- Check $_SESSION['role'] value di browser dev tools (Application tab)

## Security Notes

1. **Database**: Role column required untuk authentication
2. **Session**: Role disimpan di server session, tidak di cookie
3. **Hardening**: Optional - tambahkan requireRole() checks di admin pages
4. **Password**: Already hashed dengan password_hash(), verified dengan password_verify()
