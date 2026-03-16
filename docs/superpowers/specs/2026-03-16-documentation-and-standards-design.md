---
title: Spin-Skeleton Documentation & Standards Update
date: 2026-03-16
status: design
---

# Spin-Skeleton Documentation & Standards Update

## Overview

Improve spin-skeleton documentation and ensure full alignment with SPIN Framework coding standards. The goal is to enable new developers to:
1. Run the skeleton app locally within 5 minutes
2. See tests passing
3. Understand how to configure and extend it for their project

## Current State

**Documentation gaps:**
- README uses outdated MarkdownTOC format, focuses heavily on Apache/server configuration
- No quick-start guide for local development
- Minimal guidance on configuring databases, cache, sessions, middleware
- No testing documentation
- Two basic docs (request_lifecycle.md, template_engines.md) lack integration

**Code alignment issues:**
- Inconsistent use of `declare(strict_types=1);` across files
- Some files may not follow PSR-4 naming conventions
- Docblock completeness varies
- No standardized file header format

**Project structure:**
- Controllers: basic examples (API, web, error handlers) ✓
- Middleware: example implementations ✓
- Configuration: uses JSON config files with env var expansion ✓
- Testing: PHPUnit configured but minimal examples
- Models/persistence: no example patterns

## Solution

### 1. Documentation Rewrite

**README.md** (new, focused format)
- Remove MarkdownTOC and server configuration clutter (move to separate guide)
- Lead with: 30-second explanation, quick-start command, what's included
- Modern table of contents with guide links
- Link to SPIN Framework documentation
- Keep Apache setup info but de-emphasize (move to advanced section)

**New guides** in `doc/` (each 2-4 pages, practical examples):

1. **getting-started.md**
   - Prerequisites (PHP 8+, Composer, optional: Docker)
   - Clone and install steps
   - Running with PHP dev server
   - Verify: API health check endpoint works
   - Verify: tests pass
   - Next steps pointer

2. **configuration.md**
   - Environment variables (.env pattern)
   - Config file structure (config-{env}.json)
   - Macro expansion syntax (${env:VAR})
   - Switching environments (DEV, UNITTEST, PROD)
   - Secrets management best practices
   - Example: enabling/disabling Redis

3. **database-connections.md**
   - Connection configuration in config files
   - Example: MySQL setup
   - Example: SQLite setup
   - Using the ConnectionManager
   - PDO options reference
   - Testing with in-memory SQLite

4. **controllers-and-middleware.md**
   - Request lifecycle overview (visual reference to existing doc)
   - Creating a REST API controller
   - Creating a web page controller
   - Writing middleware (before/after pattern)
   - Middleware registration in routes
   - Error handling patterns

5. **testing.md**
   - Running the test suite
   - Test structure (mirrors src/)
   - Writing controller tests
   - Writing middleware tests
   - Mocking dependencies
   - Coverage reporting

### 2. Code Standards Alignment

**Fixes applied (committed separately):**
- Audit all PHP files for `declare(strict_types=1);` — add if missing
- Verify PSR-4 namespace mappings match directory structure
- Ensure all public methods have docblocks
- Check typed method signatures follow framework conventions
- Remove or standardize file headers

**Scope:**
- Only fix alignment issues, not refactoring
- Follow SPIN Framework CLAUDE.md conventions exactly
- Each fix is a separate commit with clear message

**What we preserve:**
- Existing architecture (already sound)
- Feature set (intentionally minimal)
- Example quality and clarity

### 3. Improvements Document

**File:** `IMPROVEMENTS.md` (review only, not committed)

Non-breaking enhancement suggestions:
- Models example class structure
- Database migration example
- CORS/rate-limiting middleware examples
- Data seeding script template
- Docker Compose setup (optional)
- GitHub Actions CI/CD workflow
- Development tools (.env.example, Makefile, etc.)
- API versioning patterns
- Database transactions examples

This document informs future work but doesn't change the skeleton in this session.

## Success Criteria

✓ New developer can clone, install, run, and verify in <5 minutes
✓ Test suite passes on fresh clone
✓ All PHP files declare strict types
✓ Documentation covers: getting started, config, database, controllers, testing
✓ IMPROVEMENTS.md provides roadmap for future enhancements
✓ Code follows SPIN Framework standards per CLAUDE.md

## Deliverables

**Documentation:**
- Rewritten README.md
- doc/getting-started.md
- doc/configuration.md
- doc/database-connections.md
- doc/controllers-and-middleware.md
- doc/testing.md

**Code:**
- Standards alignment fixes (separate commits)
- IMPROVEMENTS.md (for review)

## Out of Scope

- Architectural refactoring
- New features beyond documentation examples
- Breaking changes to skeleton structure
- Monolog vs custom logger decisions
- Template engine selection (already Plates)
- Database abstraction layer (framework uses PDO)
