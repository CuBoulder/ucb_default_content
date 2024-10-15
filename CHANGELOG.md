# CU Boulder Default Content

All notable changes to this project will be documented in this file.

Repo : [GitHub Repository](https://github.com/CuBoulder/ucb_default_content)

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

- ### Updates linter workflow
  Updates the linter workflow to use the new parent workflow in action-collection.
  
  CuBoulder/action-collection#7
  
  Sister PR in: All the things
---

- ### Create developer-sandbox-ci.yml
  new ci workflow
---

- ### refactor code to be reusable elsewhere
  Refactored code to be able to grab it easily in ucb_drush_commands. See CuBoulder/ucb_drush_commands#1
  
  closes #14
---

- ###  Prevents `/home` and `/404` pages from being indexed by Simple XML Sitemap
  It's a bit of a hack but it works. Resolves CuBoulder/ucb_default_content#12
---

- ### Fixes home page redirect (v1.3.1)
  On a new site install, the `/` path was redirecting to `/homepage` due to incorrectly setting the `system.site.page.front` setting to the node alias path rather than the node id path. This update resolves the issue.
  
  Resolves CuBoulder/ucb_default_content#9
---

- ### CU Boulder Default Content v1.3
  This update:
  - Updates the 404 page to remove the image and be editable by users with permission. Resolves CuBoulder/ucb_default_content#6
  - Reorganizes CU Boulder Default Content to be in compliance with Drupal coding standards.
  
  Sister PR in: [tiamat-theme](https://github.com/CuBoulder/tiamat-theme/pull/601)
---

- ### Adds 404 page
  Closes https://github.com/CuBoulder/tiamat-theme/issues/484. Adds the implementation of the 404 page.
---

- ### Adding in correct dependency for cu custom entities
  Resolves CuBoulder/tiamat-theme#488
---

- ### Adds `CHANGELOG.MD` and workflows; updates `core_version_requirement` to indicate D10 compatibility
  Resolves CuBoulder/ucb_default_content#2
---
