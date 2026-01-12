# Testing Guide

This project uses Cypress for end-to-end testing of the environment indicator plugin.

## Quick Start

```bash
# Install dependencies
npm install

# Start WordPress test environment
npx wp-env start

# Run tests (headless)
npm run test:e2e

# Run tests (interactive)
npm run cy:open
```

## Prerequisites

- Node.js 22+ and npm
- Docker (for wp-env)

## Setup

1. **Install dependencies:**

   ```bash
   npm install
   ```

2. **Start WordPress test environment:**

   ```bash
   npx wp-env start
   ```

   This will:
   - Start a WordPress instance at `http://localhost:8888`
   - Install the plugin automatically
   - Set up the test database

3. **Wait for WordPress to be ready**

## Running Tests

### Interactive Mode (Recommended for Development)

Open the Cypress Test Runner to run tests interactively:

```bash
npm run cy:open
```

Then select E2E Testing and choose a browser to start testing.

### Headless Mode (CI/Local Verification)

Run all tests in headless mode:

```bash
npm run test:e2e
```

Run tests with browser visible:

```bash
npm run test:e2e:headed
```

## Test Structure

### Test Files

- **`cypress/e2e/environment-indicator.cy.js`** - Tests for indicator functionality, styling, and visibility

### Custom Commands

Located in `cypress/support/commands.js`:

- `cy.wpLogin(username, password)` - Log in to WordPress admin
- `cy.wpLogout()` - Log out of WordPress
- `cy.checkEnvironmentIndicator(environment, color)` - Verify indicator exists with correct environment and optional color

## Cleaning Up

Stop the WordPress environment:

```bash
npx wp-env stop
```

Remove all containers and volumes:

```bash
npx wp-env destroy
```

## Troubleshooting

**WordPress not starting:**

- Make sure Docker is running
- Check ports 8888 and 8889 are not in use
- Try `npx wp-env destroy` then `npx wp-env start`

**Tests failing on first run:**

- Wait for WordPress to fully initialize (check `http://localhost:8888`)
- Clear Cypress cache: `npx cypress cache clear`
- Verify plugin is activated in wp-admin

## Todos

### PHP Unit Tests

Test php filters

- `dmup_environment_colors` filter
- `dmup_environment_urls` filter
- `dmup_minimum_capability` filter
- `dmup_allowed_users` filter
- Environment detection logic

## Additional Resources

- [Cypress Documentation](https://docs.cypress.io/)
- [WordPress wp-env Documentation](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/)
- [Plugin README](README.md)
