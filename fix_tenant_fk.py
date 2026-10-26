import sqlite3

conn = sqlite3.connect('database/database.sqlite')
cursor = conn.cursor()

# Get tenant info
cursor.execute("SELECT id, ulid FROM tenants WHERE slug='kedaikopi'")
tenant = cursor.fetchone()
print(f"Tenant: id={tenant[0]} ulid={tenant[1]}")

# Fix tenant_users to use INTEGER id (not ULID)
cursor.execute("UPDATE tenant_users SET tenant_id=? WHERE tenant_id!=?", (tenant[0], tenant[0]))
print(f"Updated {cursor.rowcount} tenant_users rows to use INTEGER id")

conn.commit()
conn.close()
print("Done!")
