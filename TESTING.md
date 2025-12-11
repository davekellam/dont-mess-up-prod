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
- Map the `tests/mu-plugins` directory for filter testing

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

- **`cypress/e2e/environment-indicator.cy.js`** - Tests for basic indicator functionality and visibility across all four environments
- **`cypress/e2e/filters.cy.js`** - Tests for WordPress filter customization (colors, URLs, capabilities, allowed users)

### Custom Commands

Located in `cypress/support/commands.js`:

- `cy.wpLogin(username, password)` - Log in to WordPress admin
- `cy.checkEnvironmentIndicator(environment, color)` - Verify indicator exists with correct environment and color
- `cy.checkEnvironmentSwitcher(environments)` - Verify environment switcher menu
- `cy.createUser(username, role)` - Create a test user with specific role

### Custom Tasks

Located in `cypress.config.js`:

- `cy.task('createMuPlugin', { name, content })` - Create an mu-plugin for testing filters
- `cy.task('deleteMuPlugin', name)` - Delete a test mu-plugin
- `cy.task('setEnvironmentType', envType)` - Set WP_ENVIRONMENT_TYPE constant

## Test Coverage

### Environment Indicator Tests

✅ Admin bar visibility
✅ Frontend visibility when logged in  
✅ Hidden when logged out
✅ All four environments (local, development, staging, production)
✅ Correct colors for each environment
✅ Visibility based on user capabilities
✅ Environment name capitalization

### Filter Tests

✅ `dmup_environment_colors` - Custom color overrides
✅ `dmup_environment_urls` - URL-based environment detection  
✅ `dmup_environment_urls` - Environment switcher menu
✅ `dmup_minimum_capability` - Capability-based access control
✅ `dmup_allowed_users` - Username-based access control
✅ CSS custom properties usage

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

**Filter tests not working:**
- Check that `tests/mu-plugins` directory exists
- Verify `.wp-env.json` has the correct mappings
- Restart wp-env after changing mappings

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
3. Use `cy.task()` to create mu-plugins for testing filters
4. Follow the existing test patterns for consistency
5. Run `npm run cy:open` to develop tests interactively

## Additional Resources

- [Cypress Documentation](https://docs.cypress.io/)
- [WordPress wp-env Documentation](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/)
- [Plugin README](../README.md)
