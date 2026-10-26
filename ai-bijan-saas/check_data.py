import sqlite3

conn = sqlite3.connect('database/database.sqlite')
cursor = conn.cursor()

# Check user
cursor.execute("SELECT id, email, is_platform_admin, is_active FROM users WHERE email='owner@kedaikopi.my'")
user = cursor.fetchone()
print(f"User: {user}")

# Check tenant_users
cursor.execute("SELECT * FROM tenant_users WHERE user_id=?", (user[0],))
tu = cursor.fetchone()
print(f"tenant_users: {tu}")

# Check tenant
cursor.execute("SELECT ulid, slug, status FROM tenants WHERE ulid=?", (tu[2],))
t = cursor.fetchone()
print(f"Tenant: {t}")

conn.close()
