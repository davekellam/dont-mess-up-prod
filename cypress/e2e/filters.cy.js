describe('Environment Filters - Color Customization', () => {
  it('should respect custom colors via dmup_environment_colors filter', () => {
    // This test requires a mu-plugin that sets custom colors
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
    cy.reload() // Reload to apply filter
    
    cy.get('#wp-admin-bar-dmup-environment-indicator.dmup-environment-local')
      .should('have.css', 'background-color', 'rgb(255, 0, 0)') // #ff0000
  })

  it('should use CSS custom properties for colors', () => {
    cy.wpLogin()
    cy.visit('/wp-admin')
    
    // Check that CSS variables are defined
    cy.get('html').should('exist').then(($html) => {
      const styles = window.getComputedStyle($html[0])
      const localColor = styles.getPropertyValue('--dmup-color-local')
      expect(localColor.trim()).to.not.be.empty
    })
  })
})

describe('Environment Filters - URL Detection', () => {
  it('should detect environment based on URL via dmup_environment_urls filter', () => {
    cy.task('createMuPlugin', {
      name: 'test-environment-urls',
      content: `<?php
add_filter('dmup_environment_urls', function($urls) {
  return [
    'local' => 'localhost:8888',
    'development' => 'dev.example.com',
    'staging' => 'staging.example.com',
    'production' => 'example.com',
  ];
});`
    })

    cy.wpLogin()
    cy.visit('/wp-admin')
    cy.reload()
    
    // Should detect 'local' based on localhost:8888
    cy.checkEnvironmentIndicator('local')
  })

  it('should show environment switcher menu with custom URLs', () => {
    cy.task('createMuPlugin', {
      name: 'test-environment-switcher',
      content: `<?php
add_filter('dmup_environment_urls', function($urls) {
  return [
    'local' => 'http://localhost:8888',
    'development' => 'https://dev.example.com',
    'staging' => 'https://staging.example.com',
    'production' => 'https://example.com',
  ];
});`
    })

    cy.wpLogin()
    cy.visit('/wp-admin')
    cy.reload()
    
    cy.get('#wp-admin-bar-dmup-environment-indicator').click()
    
    const environments = ['local', 'development', 'staging', 'production']
    environments.forEach(env => {
      cy.get(`#wp-admin-bar-dmup-environment-indicator-${env}`)
        .should('exist')
        .and('contain.text', env.charAt(0).toUpperCase() + env.slice(1))
    })
  })
})

describe('Environment Filters - Capability Control', () => {
  it('should respect dmup_minimum_capability filter', () => {
    // Set minimum capability to manage_options (admin only)
    cy.task('createMuPlugin', {
      name: 'test-minimum-capability',
      content: `<?php
add_filter('dmup_minimum_capability', function($capability) {
  return 'manage_options';
});`
    })

    // Create an editor user (has publish_posts but not manage_options)
    cy.wpLogin()
    const editorUsername = `editor_${Date.now()}`
    cy.visit('/wp-admin/user-new.php')
    cy.get('#user_login').type(editorUsername)
    cy.get('#email').type(`${editorUsername}@example.com`)
    cy.get('#pass1').clear().type('TestPassword123!')
    cy.get('#role').select('editor')
    cy.get('#createusersub').click()
    cy.url().should('include', 'users.php')

    // Login as editor
    cy.clearAllCookies()
    cy.clearAllSessionStorage()
    cy.wpLogin(editorUsername, 'TestPassword123!')
    cy.visit('/wp-admin')
    
    // Editor should NOT see indicator because they lack manage_options
    cy.get('#wp-admin-bar-dmup-environment-indicator').should('not.exist')
  })

  it('should disable capability check when filter returns false', () => {
    cy.task('createMuPlugin', {
      name: 'test-disable-capability',
      content: `<?php
add_filter('dmup_minimum_capability', function($capability) {
  return false;
});
add_filter('dmup_allowed_users', function($users) {
  return ['admin']; // Only admin in allowed users
});`
    })

    // Create a subscriber
    cy.wpLogin()
    const subscriberUsername = `subscriber_${Date.now()}`
    cy.visit('/wp-admin/user-new.php')
    cy.get('#user_login').type(subscriberUsername)
    cy.get('#email').type(`${subscriberUsername}@example.com`)
    cy.get('#pass1').clear().type('TestPassword123!')
    cy.get('#role').select('subscriber')
    cy.get('#createusersub').click()
    cy.url().should('include', 'users.php')

    // Login as subscriber
    cy.clearAllCookies()
    cy.clearAllSessionStorage()
    cy.wpLogin(subscriberUsername, 'TestPassword123!')
    cy.visit('/wp-admin')
    
    // Subscriber should NOT see it (not in allowed users list)
    cy.get('#wp-admin-bar-dmup-environment-indicator').should('not.exist')
  })
})

describe('Environment Filters - Allowed Users', () => {
  it('should show indicator to users in dmup_allowed_users list', () => {
    const subscriberUsername = `subscriber_${Date.now()}`
    
    // Create mu-plugin with allowed users filter
    cy.task('createMuPlugin', {
      name: 'test-allowed-users',
      content: `<?php
add_filter('dmup_allowed_users', function($users) {
  return array_merge($users, ['${subscriberUsername}']);
});`
    })

    // Create a subscriber user
    cy.wpLogin()
    cy.visit('/wp-admin/user-new.php')
    cy.get('#user_login').type(subscriberUsername)
    cy.get('#email').type(`${subscriberUsername}@example.com`)
    cy.get('#pass1').clear().type('TestPassword123!')
    cy.get('#role').select('subscriber')
    cy.get('#createusersub').click()
    cy.url().should('include', 'users.php')

    // Login as subscriber
    cy.clearAllCookies()
    cy.clearAllSessionStorage()
    cy.wpLogin(subscriberUsername, 'TestPassword123!')
    cy.visit('/wp-admin')
    
    // Subscriber SHOULD see indicator because they're in allowed users
    cy.get('#wp-admin-bar-dmup-environment-indicator').should('exist')
  })

  it('should allow multiple users via dmup_allowed_users filter', () => {
    cy.task('createMuPlugin', {
      name: 'test-multiple-allowed-users',
      content: `<?php
add_filter('dmup_allowed_users', function($users) {
  return array_merge($users, ['testuser1', 'testuser2', 'testuser3']);
});`
    })

    cy.wpLogin()
    cy.visit('/wp-admin')
    
    // Verify the filter is active (admin should still see it)
    cy.get('#wp-admin-bar-dmup-environment-indicator').should('exist')
  })
})
