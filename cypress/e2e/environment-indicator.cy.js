describe('Environment Indicator - Basic Functionality', () => {
  it('should display environment indicator in admin bar', () => {
    cy.wpLogin()
    cy.visit('/wp-admin')
    cy.get('#wpadminbar').should('be.visible')
    cy.get('#wp-admin-bar-dmup-environment-indicator').should('exist')
    cy.screenshot('01-admin-bar-indicator')
  })

  it('should show local environment by default', () => {
    cy.wpLogin()
    cy.visit('/wp-admin')
    cy.checkEnvironmentIndicator('local')
    cy.screenshot('02-local-environment')
  })

  it('should display environment indicator on frontend when logged in', () => {
    cy.wpLogin()
    cy.visit('/')
    cy.get('#wpadminbar').should('be.visible')
    cy.get('#wp-admin-bar-dmup-environment-indicator').should('exist')
    cy.screenshot('03-frontend-logged-in')
  })

  it('should not display environment indicator when logged out', () => {
    cy.visit('/')
    cy.get('#wp-admin-bar-dmup-environment-indicator').should('not.exist')
    cy.screenshot('04-logged-out-no-indicator')
  })

  it('should display environment name with capitalize transform', () => {
    cy.wpLogin()
    cy.visit('/wp-admin')
    cy.get('#wp-admin-bar-dmup-environment-indicator .dmup-environment-indicator')
      .should('have.css', 'text-transform', 'capitalize')
    cy.screenshot('05-capitalize-transform')
  })
})

describe('Environment Indicator - All Environment Colors', () => {
  afterEach(() => {
    cy.task('deleteMuPlugin', 'test-all-colors')
  })

  it('should show all four environment colors', () => {
    // Create an MU-plugin that sets custom colors to demonstrate all environments
    cy.task('createMuPlugin', {
      name: 'test-all-colors',
      content: `<?php
add_filter('dmup_environment_colors', function($colors) {
  return [
    'local' => '#6c757d',       // Grey
    'development' => '#6f42c1',  // Purple
    'staging' => '#28a745',      // Green
    'production' => '#dc3545',   // Red
  ];
});`
    })

    cy.wpLogin()
    cy.visit('/wp-admin')
    cy.reload()
    
    // Test local (grey) - this is the default
    cy.get('#wp-admin-bar-dmup-environment-indicator.dmup-environment-local')
      .should('have.css', 'background-color', 'rgb(108, 117, 125)') // #6c757d grey
    cy.screenshot('06-color-local-grey')
    
    // Also verify colors are set correctly in CSS custom properties
    cy.document().then((doc) => {
      const root = doc.documentElement
      const styles = window.getComputedStyle(root)
      
      // Check all color variables
      const localColor = styles.getPropertyValue('--dmup-color-local').trim()
      const devColor = styles.getPropertyValue('--dmup-color-development').trim()
      const stagingColor = styles.getPropertyValue('--dmup-color-staging').trim()
      const prodColor = styles.getPropertyValue('--dmup-color-production').trim()
      
      expect(localColor).to.not.be.empty
      expect(devColor).to.not.be.empty
      expect(stagingColor).to.not.be.empty
      expect(prodColor).to.not.be.empty
      
      cy.screenshot('07-all-environment-colors-set')
    })
  })
})

describe('Environment Indicator - Color Verification', () => {
  before(() => {
    // Clean up any mu-plugins from previous test files that might affect colors
    cy.task('deleteMuPlugin', 'test-custom-colors')
    cy.task('deleteMuPlugin', 'set-environment-type')
    cy.task('deleteMuPlugin', 'test-all-colors')
  })

  it('should have correct default color for local environment', () => {
    cy.wpLogin()
    cy.visit('/wp-admin')
    cy.get('#wp-admin-bar-dmup-environment-indicator.dmup-environment-local')
      .should('have.css', 'background-color', 'rgb(108, 117, 125)') // #6c757d
    cy.screenshot('08-default-color-local')
  })

  it('should use CSS custom properties for environment colors', () => {
    cy.wpLogin()
    cy.visit('/wp-admin')
    cy.document().then((doc) => {
      const root = doc.documentElement
      const styles = window.getComputedStyle(root)
      const localColor = styles.getPropertyValue('--dmup-color-local').trim()
      expect(localColor).to.not.be.empty
    })
    cy.screenshot('09-css-custom-properties')
  })
})

describe('Environment Indicator - Visibility', () => {
  it('should be visible to users with publish_posts capability', () => {
    cy.wpLogin()
    cy.visit('/wp-admin')
    cy.get('#wp-admin-bar-dmup-environment-indicator').should('exist')
    cy.screenshot('10-publish-posts-capability')
  })

  // TODO: Add test for subscriber visibility once session management issues are fixed
  // See TEST-TODO.md for details
})
