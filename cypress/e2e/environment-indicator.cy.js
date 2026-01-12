describe('Environment Indicator - Basic Functionality', () => {
  it('should display environment indicator in admin bar', () => {
    cy.wpLogin()
    cy.visit('/wp-admin')
    cy.get('#wpadminbar').should('be.visible')
    cy.get('#wp-admin-bar-dmup-environment-indicator').should('exist')
  })

  it('should show local environment by default', () => {
    cy.wpLogin()
    cy.visit('/wp-admin')
    cy.checkEnvironmentIndicator('local')
  })

  it('should display environment indicator on frontend when logged in', () => {
    cy.wpLogin()
    cy.visit('/')
    cy.get('#wpadminbar').should('be.visible')
    cy.get('#wp-admin-bar-dmup-environment-indicator').should('exist')
  })

  it('should not display environment indicator when logged out', () => {
    cy.visit('/')
    cy.get('#wp-admin-bar-dmup-environment-indicator').should('not.exist')
  })

  it('should display environment name with capitalize transform', () => {
    cy.wpLogin()
    cy.visit('/wp-admin')
    cy.get('#wp-admin-bar-dmup-environment-indicator .dmup-environment-indicator')
      .should('have.css', 'text-transform', 'capitalize')
  })
})

describe('Environment Indicator - Styling', () => {
  it('should have correct default color for local environment', () => {
    cy.wpLogin()
    cy.visit('/wp-admin')
    cy.get('#wp-admin-bar-dmup-environment-indicator.dmup-environment-local')
      .should('have.css', 'background-color', 'rgb(108, 117, 125)') // #6c757d
  })

  it('should apply correct CSS class for environment type', () => {
    cy.wpLogin()
    cy.visit('/wp-admin')
    cy.get('#wp-admin-bar-dmup-environment-indicator')
      .should('have.class', 'dmup-environment-local')
  })

  it('should use CSS custom properties for environment colors', () => {
    cy.wpLogin()
    cy.visit('/wp-admin')
    cy.document().then((doc) => {
      const root = doc.documentElement
      const styles = window.getComputedStyle(root)
      
      // Verify all environment color variables are defined
      const localColor = styles.getPropertyValue('--dmup-color-local').trim()
      const devColor = styles.getPropertyValue('--dmup-color-development').trim()
      const stagingColor = styles.getPropertyValue('--dmup-color-staging').trim()
      const prodColor = styles.getPropertyValue('--dmup-color-production').trim()
      
      expect(localColor).to.not.be.empty
      expect(devColor).to.not.be.empty
      expect(stagingColor).to.not.be.empty
      expect(prodColor).to.not.be.empty
    })
  })
})

describe('Environment Indicator - Visibility', () => {
  it('should be visible to admin users', () => {
    cy.wpLogin()
    cy.visit('/wp-admin')
    cy.get('#wp-admin-bar-dmup-environment-indicator').should('exist').and('be.visible')
  })

  it('should be visible on both admin and frontend when logged in', () => {
    cy.wpLogin()
    
    // Check admin
    cy.visit('/wp-admin')
    cy.get('#wp-admin-bar-dmup-environment-indicator').should('exist')
    
    // Check frontend
    cy.visit('/')
    cy.get('#wp-admin-bar-dmup-environment-indicator').should('exist')
  })
})
