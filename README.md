# Laravel Multi-Tenancy & Permissions Package

A professional-grade multi-tenancy and role-based permissions package for Laravel 12, designed for senior developers with enterprise requirements.

## Features

- **Multi-Tenancy**
  - Database-per-tenant architecture
  - Multiple tenant identification methods (header, domain, subdomain)
  - Tenant-specific settings and feature flags
  - Automatic database creation and migration
  - Database backup and restore capabilities

- **Role-Based Permissions**
  - Hierarchical permissions system
  - Wildcard permissions support (e.g., `users.*`)
  - Permission caching for performance
  - Role-permission relationships
  - User-role relationships

- **Security**
  - Strict tenant isolation
  - Rate limiting for authentication and permission checks
  - Encryption service for sensitive data
  - Audit logging for all actions
  - Event-driven architecture for monitoring

- **Performance**
  - Comprehensive caching strategies
  - Database query optimization
  - Efficient memory usage
  - Redis support for distributed caching

- **Developer Experience**
  - Repository pattern for data access
  - Strategy pattern for tenant identification
  - Factory pattern for tenant creation
  - Comprehensive test suite
  - Detailed documentation

## Installation

```bash
composer require esmat/laravel-multitenant-permission

