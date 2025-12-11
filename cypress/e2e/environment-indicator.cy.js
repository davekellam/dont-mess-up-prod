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

describe('Environment Indicator - Color Verification', () => {
  it('should have correct default color for local environment', () => {
    cy.wpLogin()
    cy.visit('/wp-admin')
    cy.get('#wp-admin-bar-dmup-environment-indicator.dmup-environment-local')
      .should('have.css', 'background-color', 'rgb(108, 117, 125)') // #6c757d
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
  })
})

describe('Environment Indicator - Visibility', () => {
  it('should be visible to users with publish_posts capability', () => {
    cy.wpLogin()
    cy.visit('/wp-admin')
    cy.get('#wp-admin-bar-dmup-environment-indicator').should('exist')
  })

  it('should not be visible to subscribers by default', () => {
    // Create a subscriber user
    cy.wpLogin() // Login as admin first
    const subscriberUsername = `subscriber_${Date.now()}`
    
    cy.visit('/wp-admin/user-new.php')
    cy.get('#user_login').type(subscriberUsername)
    cy.get('#email').type(`${subscriberUsername}@example.com`)
    cy.get('#pass1').clear().type('TestPassword123!')
    cy.get('#role').select('subscriber')
    cy.get('#createusersub').click()
    cy.url().should('include', 'users.php')

    // Logout and login as subscriber
    cy.clearAllCookies()
    cy.clearAllSessionStorage()
    cy.wpLogin(subscriberUsername, 'TestPassword123!')
    cy.visit('/wp-admin')
    
    // Subscriber shouldn't see the indicator by default
    cy.get('#wp-admin-bar-dmup-environment-indicator').should('not.exist')
  })
})
