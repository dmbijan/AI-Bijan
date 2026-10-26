import sqlite3

conn = sqlite3.connect('database/database.sqlite')
cursor = conn.cursor()

# Get the correct ULID for kedaikopi tenant
cursor.execute("SELECT ulid FROM tenants WHERE slug='kedaikopi'")
tenant = cursor.fetchone()
if tenant:
    ulid = tenant[0]
    print(f"Found tenant ULID: {ulid}")
    
    # Update all tenant_users to use correct ULID
    cursor.execute("UPDATE tenant_users SET tenant_id=? WHERE tenant_id=1", (ulid,))
    print(f"Updated {cursor.rowcount} tenant_users rows")
    
    conn.commit()
else:
    print("Tenant not found!")

conn.close()
print("Done!")
