-- Give the original active administrator owner access, only if no active owner exists.
UPDATE admin_users SET role='super_admin'
WHERE id=(SELECT id FROM (SELECT id FROM admin_users WHERE status='active' ORDER BY id LIMIT 1) AS first_admin)
AND NOT EXISTS (SELECT id FROM (SELECT id FROM admin_users WHERE role='super_admin' AND status='active') AS owners);
