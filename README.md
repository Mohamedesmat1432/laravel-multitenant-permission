# Laravel Multi-Tenancy & RBAC Package

A professional-grade multi-tenancy and role-based access control (RBAC) package for Laravel 12 API projects.

## Features

### Multi-Tenancy
- Database-per-tenant architecture
- Multiple tenant identification methods (header, domain, subdomain, path)
- Tenant-specific settings and feature flags
- Automatic database creation and migration
- Tenant isolation

### Role-Based Access Control (RBAC)
- Hierarchical permissions system
- Wildcard permissions support (e.g., users.*)
- Permission caching for performance
- Role-permission relationships
- User-role relationships

### API-First Design
- RESTful API endpoints for all models
- JSON responses with proper status codes
- Authentication with Laravel Sanctum
- Authorization with middleware

### Security
- Strict tenant isolation
- Rate limiting for authentication and permission checks
- Audit logging for all actions
- Event-driven architecture for monitoring

### Performance
- Comprehensive caching strategies
- Database query optimization
- Efficient memory usage
- Redis support for distributed caching

## Installation

1. Install the package via Composer:
```bash
composer require elgaml/laravel-multi-tenancy-rbac
