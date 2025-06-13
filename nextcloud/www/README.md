# GTC Self-Service Password System

This system allows staff and students to:
- 🔐 Change their own passwords
- 🔄 Request a password reset (if forgotten)
- ⚙️ Let administrators view requests and perform bulk user imports

> ✅ Built with PHP, LDAP, Smarty, AdminLTE, and Docker  
> 🏫 Designed for Active Directory domains `staff.gtc.ce.ac.bw` and `students.gtc.ce.ac.bw`

---

## 🧭 Features Overview

| Feature | Description | URL |
|--------|-------------|-----|
| 🔐 Change Password | Users can change known passwords | `/change_password.php` |
| ❓ Reset Request | Users can request a reset if they forgot password | `/reset_request.php` |
| 🧑‍💼 Admin Login | Authenticates AD admins only | `/admin_login.php` |
| 📊 Admin Dashboard | Shows reset requests, user import tools | `/admin_dashboard.php` |
| 📁 User Import | Bulk add AD users from Excel | `/ldap_user_import.php` |
| 🔄 Password Approvals | Process pending reset requests | `/update_password.php` |

---

## 🔒 Security Features

- ✅ LDAP Authentication (LDAPS)
- ✅ Group check: only `CN=ADMINS,OU=ADMINS,...` can log in as admin
- ✅ CSRF token on all POST forms
- ✅ Session timeout auto logout (default: 15 mins)
- ✅ Docker container isolation

---

## ⚙️ File Structure

