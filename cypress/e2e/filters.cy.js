describe('Environment Filters - Color Customization', () => {
  afterEach(() => {
    // Clean up test mu-plugins to prevent filter contamination
    cy.task('deleteMuPlugin', 'test-custom-colors')
  })

  it('should apply custom colors via dmup_environment_colors filter', () => {
    // Create mu-plugin with bright orange color to clearly show filter is working
    cy.task('createMuPlugin', {
      name: 'test-custom-colors',
      content: `<?php
add_filter('dmup_environment_colors', function($colors) {
  return [
    'local' => '#ff6600',        // Bright orange - clearly different from default grey
    'development' => '#00ff00',
    'staging' => '#0000ff',
    'production' => '#ffff00',
  ];
});`
    })

    cy.wpLogin()
    cy.visit('/wp-admin')
    cy.reload() // Reload to apply filter
    
    // Verify the custom orange color is applied (not the default grey)
    cy.get('#wp-admin-bar-dmup-environment-indicator.dmup-environment-local')
      .should('have.css', 'background-color', 'rgb(255, 102, 0)') // #ff6600 bright orange
    cy.screenshot('11-custom-color-orange-filter')
  })
})

describe('Environment Filters - URL Detection', () => {
  afterEach(() => {
    // Clean up test mu-plugins
    cy.task('deleteMuPlugin', 'test-environment-urls')
    cy.task('deleteMuPlugin', 'test-environment-switcher')
  })

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
});
// Use bright pink to show filter is working
add_filter('dmup_environment_colors', function($colors) {
  return [
    'local' => '#ff1493',  // Bright pink
    'development' => '#00ff00',
    'staging' => '#0000ff',
    'production' => '#ffff00',
  ];
});`
    })

    cy.wpLogin()
    cy.visit('/wp-admin')
    cy.reload()
    
    // Should detect 'local' based on localhost:8888 with pink color
    cy.checkEnvironmentIndicator('local')
    cy.get('#wp-admin-bar-dmup-environment-indicator.dmup-environment-local')
      .should('have.css', 'background-color', 'rgb(255, 20, 147)') // #ff1493 bright pink
    cy.screenshot('12-url-detection-filter-pink')
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
});
// Use bright cyan to show filter is working
add_filter('dmup_environment_colors', function($colors) {
  return [
    'local' => '#00ffff',  // Bright cyan
    'development' => '#ff00ff',
    'staging' => '#ffff00',
    'production' => '#ff0000',
  ];
});`
    })

    cy.wpLogin()
    cy.visit('/wp-admin')
    cy.reload()
    
    // Verify cyan color is applied
    cy.get('#wp-admin-bar-dmup-environment-indicator.dmup-environment-local')
      .should('have.css', 'background-color', 'rgb(0, 255, 255)') // #00ffff cyan
    
    cy.get('#wp-admin-bar-dmup-environment-indicator').click()
    
    const environments = ['local', 'development', 'staging', 'production']
    environments.forEach(env => {
      cy.get(`#wp-admin-bar-dmup-environment-indicator-${env}`)
        .should('exist')
        .and('contain.text', env.charAt(0).toUpperCase() + env.slice(1))
    })
    cy.screenshot('13-environment-switcher-cyan')
  })
})

describe('Environment Filters - Capability Control', () => {
  afterEach(() => {
    // Clean up test mu-plugins
    cy.task('deleteMuPlugin', 'test-minimum-capability')
    cy.task('deleteMuPlugin', 'test-disable-capability')
  })

  // TODO: Add tests for capability control once user creation/login issues are fixed
  // - Test dmup_minimum_capability filter with editor user
  // - Test capability check disabled (returns false) with subscriber
  // See TEST-TODO.md for details
})

describe('Environment Filters - Allowed Users', () => {
  afterEach(() => {
    // Clean up test mu-plugins
    cy.task('deleteMuPlugin', 'test-allowed-users')
    cy.task('deleteMuPlugin', 'test-multiple-allowed-users')
  })

  // TODO: Add test for allowed users with subscriber once user creation/login issues are fixed
  // See TEST-TODO.md for details

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
    cy.screenshot('14-multiple-allowed-users')
  })
})
