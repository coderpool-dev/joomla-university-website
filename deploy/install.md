# Deployment Notes

These notes describe the sanitized deployment flow used for the public case.

1. Copy the project files to the web root, for example `/var/www/example`.
2. Create a MySQL database and user for Joomla.
3. Copy `configuration.example.php` to `configuration.php`.
4. Fill in the local database name, user, password, site name, paths and secret.
5. Configure nginx or Apache to point to the project directory.
6. Set the web server user as owner for writable directories.
7. Import a local development database dump if you have one.
8. Verify routing, uploads, cache and administrator access in the target environment.

Production credentials, SQL backups and uploaded organization files must not be committed.
