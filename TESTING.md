# E2E Testing with Cypress

This project uses Cypress for end-to-end testing of the environment indicator plugin.

## Prerequisites

- Node.js 18+ and npm
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

3. **Wait for WordPress to be ready** (usually takes 30-60 seconds on first run)

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

## Test Coverage

### What We Test

✅ Admin bar visibility
✅ Frontend visibility when logged in  
✅ Hidden when logged out
✅ Default local environment display
✅ Correct default colors
✅ CSS custom properties defined
✅ Environment name capitalization
✅ Correct CSS classes applied

### What We Don't Test in E2E

❌ Filter API (colors, URLs, capabilities) - These are better suited for PHP unit tests
❌ Multiple environment types - Would require restarting wp-env with different configs
❌ User role/permission variations - Complex session management in Cypress

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

## CI/CD Integration

To run tests in CI (GitHub Actions, etc.):

```yaml
- name: Install Node dependencies
  run: npm ci

- name: Start WordPress environment
  run: npx wp-env start

- name: Run E2E tests
  run: npm run test:e2e
```

## Writing New Tests

1. Create a new test file in `cypress/e2e/`
2. Use the custom commands for common operations
3. Follow the existing test patterns for consistency
4. Run `npm run cy:open` to develop tests interactively

## Additional Resources

- [Cypress Documentation](https://docs.cypress.io/)
- [WordPress wp-env Documentation](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/)
- [Plugin README](../README.md)
