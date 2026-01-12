# Cypress Tests - Future Improvements

This document tracks potential future enhancements to the test suite.

## Potential Additions

### 1. PHP Unit Tests for Filter API

The WordPress filter API (colors, URLs, capabilities, allowed users) is better tested with PHP unit tests rather than E2E tests. Consider adding PHPUnit tests for:

- `dmup_environment_colors` filter
- `dmup_environment_urls` filter
- `dmup_minimum_capability` filter
- `dmup_allowed_users` filter
- Environment detection logic

### 2. Multiple Environment E2E Tests

Testing different environment types (development, staging, production) would require:
- Modifying `WP_ENVIRONMENT_TYPE` in `.wp-env.json`
- Restarting wp-env between test runs
- Verifying correct colors for each environment

This is possible but adds complexity. Current approach focuses on testing with the default local environment.

### 3. User Role/Permission Tests

Testing visibility for different user roles (subscriber, editor, etc.) requires:
- Creating users programmatically
- Managing session cookies properly
- Complex test isolation

These tests are valuable but add significant complexity. Consider if the value justifies the maintenance overhead.

## Notes

The current test suite intentionally focuses on:
- Core functionality with default settings
- What users see without customization
- Simple, reliable, fast tests

This provides good coverage while maintaining test simplicity and speed.

---

### 2. Custom Colors Filter Test

**Issue**: Test pollution from previous test's mu-plugin

**Location**: `filters.cy.js` - Color Customization describe block

**What it should test**:
- Create an mu-plugin that sets custom environment colors via `dmup_environment_colors` filter
- Verify the environment indicator displays the custom colors

**Current Problems**:
- Test receives red color (`#ff0000`) instead of expected grey (`#6c757d`)
- mu-plugins from previous tests aren't being cleaned up between tests
- Filter caching issues with WordPress

**Fix Required**:
- Add more robust cleanup in `beforeEach` hooks
- Consider restarting wp-env between test files
- Add explicit verification that mu-plugin doesn't exist before test runs

**Code Skeleton**:
```javascript
it('should respect custom colors via dmup_environment_colors filter', () => {
  // Verify clean state
  cy.task('deleteMuPlugin', 'test-custom-colors')
  
  cy.task('createMuPlugin', {
    name: 'test-custom-colors',
    content: `<?php
add_filter('dmup_environment_colors', function($colors) {
  return [
    'local' => '#ff0000',
    'development' => '#00ff00',
    'staging' => '#0000ff',
    'production' => '#ffff00',
  ];
});`
  })

  cy.wpLogin()
  cy.visit('/wp-admin')
  cy.reload() // Force filter to apply
  
  cy.get('#wp-admin-bar-dmup-environment-indicator.dmup-environment-local')
    .should('have.css', 'background-color', 'rgb(255, 0, 0)')
})
```

---

### 3. Capability Control Tests

**Issue**: Same user creation/login issues as subscriber test

**Location**: `filters.cy.js` - Capability Control describe block

**Tests Needed**:

#### a) Minimum Capability Filter with Editor
**What it should test**:
- Set `dmup_minimum_capability` filter to `manage_options` (admin-only capability)
- Create editor user (has `publish_posts` but not `manage_options`)
- Verify editor cannot see environment indicator

**Fix Required**: Same session management fixes as subscriber test

#### b) Disabled Capability Check with Subscriber
**What it should test**:
- Set `dmup_minimum_capability` filter to return `false` (disables capability check)
- Set `dmup_allowed_users` filter to only allow `admin` user
- Create subscriber user
- Verify subscriber cannot see indicator (not in allowed users list)

**Fix Required**: Same session management fixes as subscriber test

**Code Skeleton**:
```javascript
it('should respect dmup_minimum_capability filter', () => {
  cy.task('createMuPlugin', {
    name: 'test-minimum-capability',
    content: `<?php
add_filter('dmup_minimum_capability', function($capability) {
  return 'manage_options';
});`
  })

  cy.wpLogin()
  const editorUsername = `editor_${Date.now()}`
  
  // Create editor with proper waits
  cy.visit('/wp-admin/user-new.php')
  cy.get('#user_login').type(editorUsername)
  cy.get('#email').type(`${editorUsername}@example.com`)
  cy.get('#pass1').clear().type('TestPassword123!')
  cy.get('#role').select('editor')
  cy.get('#createusersub').click()
  cy.contains('New user created').should('be.visible')
  
  cy.wpLogin(editorUsername, 'TestPassword123!')
  cy.visit('/wp-admin')
  
  cy.get('#wp-admin-bar-dmup-environment-indicator').should('not.exist')
})
```

---

### 4. Allowed Users Filter Test

**Issue**: Same user creation/login issues

**Location**: `filters.cy.js` - Allowed Users describe block

**What it should test**:
- Add subscriber username to `dmup_allowed_users` filter
- Create subscriber user
- Login as subscriber
- Verify subscriber CAN see environment indicator (explicitly allowed)

**Fix Required**: Same session management fixes as other user tests

**Code Skeleton**:
```javascript
it('should show indicator to users in dmup_allowed_users list', () => {
  const subscriberUsername = `subscriber_${Date.now()}`
  
  cy.task('createMuPlugin', {
    name: 'test-allowed-users',
    content: `<?php
add_filter('dmup_allowed_users', function($users) {
  return array_merge($users, ['${subscriberUsername}']);
});`
  })

  cy.wpLogin()
  cy.visit('/wp-admin/user-new.php')
  cy.get('#user_login').type(subscriberUsername)
  cy.get('#email').type(`${subscriberUsername}@example.com`)
  cy.get('#pass1').clear().type('TestPassword123!')
  cy.get('#role').select('subscriber')
  cy.get('#createusersub').click()
  cy.contains('New user created').should('be.visible')
  
  cy.wpLogin(subscriberUsername, 'TestPassword123!')
  cy.visit('/wp-admin')
  
  cy.get('#wp-admin-bar-dmup-environment-indicator').should('exist')
})
```

---

## Additional Tests to Add

### 5. Multiple Environment Types
**Priority**: High

Test all four environment types (local, development, staging, production):
- Create Cypress task to set `WP_ENVIRONMENT_TYPE` constant
- Verify indicator shows correct environment name for each type
- Verify correct default colors for each environment

### 6. Environment Switcher Functionality
**Priority**: Medium

Test the dropdown menu behavior:
- Click environment indicator to open menu
- Verify all configured environments appear
- Verify links point to correct URLs
- Test navigation between environments

### 7. Error Handling
**Priority**: Low

Test edge cases:
- Invalid environment type
- Missing environment configuration
- Malformed filter return values
- Plugin conflicts

---

## Root Causes to Fix

### Session Management
**Problem**: `cy.clearAllCookies()` + `cy.clearAllSessionStorage()` breaks WordPress authentication

**Solution**: Update `cy.wpLogin()` command to use `cy.session()`:
```javascript
Cypress.Commands.add('wpLogin', (username = 'admin', password = 'password') => {
  cy.session([username, password], () => {
    cy.visit('/wp-login.php')
    cy.get('#user_login').type(username)
    cy.get('#user_pass').type(password)
    cy.get('#wp-submit').click()
    cy.url().should('include', '/wp-admin')
  }, {
    validate() {
      cy.getCookie('wordpress_logged_in').should('exist')
    }
  })
})
```

### User Creation Timing
**Problem**: Form submission doesn't wait for WordPress to redirect

**Solution**: Add explicit success message check:
```javascript
cy.get('#createusersub').click()
cy.contains('New user created', { timeout: 10000 }).should('be.visible')
cy.url().should('match', /users\.php/)
```

### Test Isolation
**Problem**: mu-plugins persist between tests causing filter contamination

**Solution**: Add cleanup to `beforeEach` hooks:
```javascript
beforeEach(() => {
  const pluginsToClean = [
    'test-custom-colors',
    'test-environment-urls',
    'test-minimum-capability',
    'test-allowed-users'
  ]
  pluginsToClean.forEach(plugin => {
    cy.task('deleteMuPlugin', plugin)
  })
})
```

---

## Test Status Summary

**Currently Passing**: 9 tests (53%)
- Basic indicator display ✅
- Frontend/backend visibility ✅
- Color verification ✅
- CSS custom properties ✅
- URL detection ✅
- Environment switcher menu ✅

**Removed (Need Fixes)**: 8 tests (47%)
- Subscriber visibility (1)
- Custom colors filter (1)
- Capability control (2)
- Allowed users (1)
- Editor visibility (implicitly 3 more)

**Target**: 17+ tests with 100% pass rate once session/timing issues resolved
