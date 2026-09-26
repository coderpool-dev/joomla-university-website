# ROSNEW Joomla Migration Case

Public portfolio version of a university website migration and deployment project.

This repository is a sanitized case study. It keeps the technical structure needed to discuss the work, while removing production data, database dumps, secrets, admin logs, cache files, uploaded documents, and media assets owned by the organization.

## Scope

- migrated and prepared a Joomla-based university website for deployment;
- configured PHP-FPM, nginx, MySQL, caching and clean URLs;
- preserved public content structure during migration;
- prepared installation notes and deployment configuration;
- removed private data and organization-owned files from the public version.

## What Is Removed

- `configuration.php` with database credentials and Joomla secret;
- SQL backups and installation archives;
- user uploads, public documents, photos, schedules and scans;
- cache, temporary files and logs;
- admin-only data and environment-specific paths.

## Public Placeholders

The folders `images`, `download`, `upload`, `uploads`, `data`, `files` and `virtual-tours` contain placeholders instead of real files. In the production project these folders contained public website materials owned or published by the university.

## Deployment

Use `configuration.example.php` as a safe template for local setup. Real credentials, domain names and secrets must be provided through an environment-specific configuration file that is never committed.

The project was originally deployed with nginx, PHP-FPM and MySQL. A sanitized nginx example can be added under `deploy/` when needed.
