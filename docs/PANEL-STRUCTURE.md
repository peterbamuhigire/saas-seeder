# SaaS Seeder - Panel Structure Guide

## 🏗️ Three-Tier Panel Architecture

The SaaS Seeder uses a **three-tier panel structure** to support multi-tenant SaaS applications with end users.

### 📊 Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                     SUPER ADMIN PANEL                        │
│                   /public/adminpanel/                        │
│                                                              │
│  • System-wide management                                   │
│  • Manage multiple franchises/schools/organizations         │
│  • Global settings & configurations                         │
│  • User: super_admin                                        │
└─────────────────────────────────────────────────────────────┘
                            ▲
                            │
                     manages multiple
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                   FRANCHISE ADMIN PANEL                      │
│                      /public/ (root)                         │
│                                                              │
│  • Franchise-specific management                            │
│  • Manage franchise users & data                            │
│  • Franchise settings & configuration                       │
│  • User: owner, staff (with permissions)                    │
└─────────────────────────────────────────────────────────────┘
                            ▲
                            │
                      manages users
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                      END USER PANEL                          │
│                  /public/memberpanel/                        │
│                                                              │
│  • End user self-service portal                             │
│  • Access personal data & features                          │
│  • Limited permissions                                      │
│  • User: member, student, customer, etc.                    │
└─────────────────────────────────────────────────────────────┘
```

---

## 🎯 Use Cases by Panel

### 1. Super Admin Panel (`/adminpanel/`)

**Who uses it:** System administrators, SaaS operators

**Real-world examples:**
- **School SaaS:** Platform owner managing multiple schools
- **Restaurant SaaS:** SaaS company managing multiple restaurant franchises
- **Medical SaaS:** Platform admin managing multiple clinics

**Typical pages:**
- `/adminpanel/franchises/` - List and manage all franchises
- `/adminpanel/users/` - Manage super admin users
- `/adminpanel/billing/` - Global billing and subscriptions
- `/adminpanel/system-settings/` - System-wide configurations
- `/adminpanel/reports/` - Cross-franchise analytics

**User types:** `super_admin`

**Access control:**
```php
// Only super admins can access
if (getSession('user_type') !== 'super_admin') {
    header('Location: ../index.php');
    exit();
}
```

---

### 2. Franchise Admin Panel (`/public/` root)

**Who uses it:** Franchise owners, school admins, restaurant managers

**Real-world examples:**
- **School SaaS:** Principal managing their school (students, teachers, classes)
- **Restaurant SaaS:** Restaurant manager managing their location (staff, menu, orders)
- **Medical SaaS:** Clinic manager managing their clinic (doctors, patients, appointments)

**Typical pages:**
- `/dashboard.php` - Franchise-specific dashboard
- `/students/` or `/customers/` - Manage end users
- `/staff.php` - Manage franchise staff
- `/settings.php` - Franchise settings
- `/reports.php` - Franchise-specific reports

**User types:** `owner`, `staff` (with appropriate permissions)

**Access control:**
```php
// Require franchise-level authentication
requireAuth();

// Ensure user belongs to a franchise (not super admin)
if (getSession('user_type') === 'super_admin') {
    // Super admins can view but may need to select a franchise first
}

// Check permissions for specific actions
requirePermissionGlobal('MANAGE_STUDENTS');
```

---

### 3. End User Panel (`/memberpanel/`)

**Who uses it:** End users, students, customers, patients

**Real-world examples:**
- **School SaaS:** Students viewing grades, assignments, attendance
- **Restaurant SaaS:** Customers viewing loyalty points, order history
- **Medical SaaS:** Patients viewing appointments, medical records

**Typical pages:**
- `/memberpanel/dashboard.php` - Personal dashboard
- `/memberpanel/profile.php` - Update personal information
- `/memberpanel/grades.php` (school) - View grades
- `/memberpanel/orders.php` (restaurant) - View order history
- `/memberpanel/appointments.php` (medical) - View appointments

**User types:** Custom types like `student`, `customer`, `patient`, or generic `member`

**Access control:**
```php
// Require authentication
requireAuth();

// Users can only see their own data
$userId = getSession('user_id');
$franchiseId = getSession('franchise_id');

// Filter queries by user
$query = "SELECT * FROM grades WHERE user_id = ? AND franchise_id = ?";
```

---

## 🚦 Routing & Redirection Rules

### Login Redirect Logic (`index.php`)

```php
$userType = getSession('user_type');

if ($userType === 'super_admin') {
    // Super admin → System admin panel
    header('Location: ./adminpanel/');
} elseif ($userType === 'owner' || $userType === 'staff') {
    // Franchise admin → Root public/ pages
    header('Location: ./dashboard.php');
} else {
    // End users (student, customer, etc.) → Member panel
    header('Location: ./memberpanel/');
}
```

### Access Control Rules

**Super Admin:**
- ✅ Can access `/adminpanel/` (default)
- ✅ Can access `/public/` root (to manage specific franchises)
- ✅ Can access `/memberpanel/` (to test end-user experience)

**Franchise Owner/Staff:**
- ❌ Cannot access `/adminpanel/` → Redirected to `/dashboard.php`
- ✅ Can access `/public/` root (their workspace)
- ❌ Should not access `/memberpanel/` (unless they're also an end user)

**End Users (Members):**
- ❌ Cannot access `/adminpanel/`
- ❌ Cannot access `/public/` root (franchise admin pages)
- ✅ Can only access `/memberpanel/`

---

## 📁 File Structure

```
saas-seeder/
├── public/
│   ├── index.php                    # Smart router
│   ├── sign-in.php                  # Universal login
│   ├── dashboard.php                # 🏫 FRANCHISE ADMIN DASHBOARD
│   ├── students.php                 # 🏫 Manage students (franchise)
│   ├── staff.php                    # 🏫 Manage staff (franchise)
│   ├── reports.php                  # 🏫 Franchise reports
│   ├── settings.php                 # 🏫 Franchise settings
│   │
│   ├── adminpanel/                  # 🌐 SUPER ADMIN PANEL
│   │   ├── index.php               # Super admin dashboard
│   │   ├── franchises.php          # Manage franchises/schools
│   │   ├── system-settings.php     # Global settings
│   │   └── billing.php             # System billing
│   │
│   └── memberpanel/                 # 👤 END USER PANEL
│       ├── index.php               # Student/member dashboard
│       ├── profile.php             # Personal profile
│       ├── grades.php              # View grades (school)
│       └── assignments.php         # View assignments (school)
```

---

## 🎓 Example: School Management SaaS

### Scenario
You're building a SaaS platform for managing multiple schools.

### Users & Their Panels

1. **Platform Owner (You)**
   - User Type: `super_admin`
   - Panel: `/adminpanel/`
   - Can: Add new schools, manage subscriptions, view all schools

2. **School Principal (Client)**
   - User Type: `owner`
   - Panel: `/public/` root (franchise admin)
   - Can: Manage their school's students, teachers, classes, reports

3. **School Admin Staff**
   - User Type: `staff`
   - Panel: `/public/` root (franchise admin)
   - Can: Limited permissions (e.g., view students, create assignments)

4. **Students**
   - User Type: `student` (or `member`)
   - Panel: `/memberpanel/`
   - Can: View their grades, assignments, attendance

### File Locations

```
/public/adminpanel/schools.php        # Super admin manages schools
/public/dashboard.php                  # School principal's dashboard
/public/students.php                   # School principal manages students
/public/classes.php                    # School principal manages classes
/public/memberpanel/grades.php        # Students view their grades
/public/memberpanel/assignments.php   # Students view assignments
```

---

## 🍽️ Example: Restaurant Management SaaS

### Users & Their Panels

1. **Platform Owner**
   - User Type: `super_admin`
   - Panel: `/adminpanel/`
   - Can: Add new restaurant locations, manage subscriptions

2. **Restaurant Manager**
   - User Type: `owner`
   - Panel: `/public/` root
   - Can: Manage their location's staff, menu, orders, inventory

3. **Restaurant Staff**
   - User Type: `staff`
   - Panel: `/public/` root
   - Can: Take orders, view menu (limited permissions)

4. **Customers**
   - User Type: `customer` (or `member`)
   - Panel: `/memberpanel/`
   - Can: View order history, loyalty points, place orders online

---

## 🔒 Security Best Practices

### 1. Always Check User Type

```php
// In franchise admin pages (public/ root)
if (getSession('user_type') === 'super_admin') {
    // Allow but may need franchise context
} elseif (getSession('user_type') === 'owner' || getSession('user_type') === 'staff') {
    // Allow franchise admins
} else {
    // Redirect end users to memberpanel
    header('Location: ./memberpanel/');
    exit();
}
```

### 2. Always Filter by Franchise ID

```php
// In franchise admin pages
$franchiseId = getSession('franchise_id');

// Only show data for this franchise
$stmt = $db->prepare("SELECT * FROM students WHERE franchise_id = ?");
$stmt->execute([$franchiseId]);
```

### 3. End Users Only See Their Data

```php
// In memberpanel pages
$userId = getSession('user_id');
$franchiseId = getSession('franchise_id');

// Only show this user's data
$stmt = $db->prepare("SELECT * FROM grades WHERE user_id = ? AND franchise_id = ?");
$stmt->execute([$userId, $franchiseId]);
```

---

## 🎨 Customization Tips

### Rename "Member Panel" for Your Use Case

In `/memberpanel/`, update branding:

```php
// For school:
$pageTitle = 'Student Portal';

// For restaurant:
$pageTitle = 'Customer Portal';

// For medical:
$pageTitle = 'Patient Portal';
```

### Custom User Types

Add custom user types in database:

```sql
ALTER TABLE tbl_users MODIFY user_type ENUM(
  'super_admin',
  'owner',
  'staff',
  'student',      -- For schools
  'customer',     -- For restaurants
  'patient'       -- For medical
) NOT NULL DEFAULT 'staff';
```

---

## 📝 Summary

| Panel | Location | Who | Purpose |
|-------|----------|-----|---------|
| **Super Admin** | `/adminpanel/` | Platform owners | Manage entire SaaS system |
| **Franchise Admin** | `/public/` (root) | Franchise owners/staff | Manage their franchise |
| **End User** | `/memberpanel/` | Students/customers/patients | Self-service portal |

**Key Principle:**
- **Root `/public/`** = Franchise admin workspace (school principal, restaurant manager)
- **`/memberpanel/`** = End user portal (students, customers, patients)
- **`/adminpanel/`** = Super admin system (SaaS operator)

---

**Generated:** 2026-02-02
**Version:** 1.0
