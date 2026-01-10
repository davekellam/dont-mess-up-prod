/**
 * Cypress custom command to log in to WordPress admin
 */
Cypress.Commands.add('wpLogin', (username, password) => {
  const user = username || Cypress.env('wpUsername')
  const pass = password || Cypress.env('wpPassword')
  
  cy.getCookies().then(cookies => {
    let hasMatch = false
    cookies.forEach((cookie) => {
      if (cookie.name.substr(0, 20) === 'wordpress_logged_in_') {
        hasMatch = true
      }
    })
    
    if (!hasMatch) {
      cy.visit('/wp-login.php').wait(1000)
      cy.get('#user_login').type(user)
      cy.get('#user_pass').type(`${pass}{enter}`)
    }
  })
})

/**
 * Cypress custom command to log out of WordPress
 */
Cypress.Commands.add('wpLogout', () => {
  cy.getCookies().then(cookies => {
    cookies.forEach(cookie => {
      cy.clearCookie(cookie.name)
    })
  })
})

/**
 * Custom command to check if environment indicator exists
 */
Cypress.Commands.add('checkEnvironmentIndicator', (environment, color) => {
  cy.get('#wp-admin-bar-dmup-environment-indicator')
    .should('exist')
    .and('be.visible')
    .within(() => {
      cy.get('.dmup-environment-indicator')
        .should('contain.text', environment)
    })
  
  cy.get('#wp-admin-bar-dmup-environment-indicator')
    .should('have.class', `dmup-environment-${environment}`)
  
  if (color) {
    cy.get(`#wp-admin-bar-dmup-environment-indicator.dmup-environment-${environment}`)
      .should('have.css', 'background-color', color)
  }
})

/**
 * Custom command to check environment switcher menu
 */
Cypress.Commands.add('checkEnvironmentSwitcher', (environments) => {
  cy.get('#wp-admin-bar-dmup-environment-indicator').click()
  
  environments.forEach(env => {
    cy.get(`#wp-admin-bar-dmup-environment-indicator-${env}`)
      .should('exist')
      .and('be.visible')
  })
})

/**
 * Custom command to create a test user with specific role
 */
Cypress.Commands.add('createUser', (username, role = 'editor') => {
  cy.wpLogin()
  cy.visit('/wp-admin/user-new.php')
  cy.get('#user_login').type(username)
  cy.get('#email').type(`${username}@example.com`)
  
  // Use the "Show password" button instead of typing in hidden pass2
  cy.get('#pass1').type('TestPassword123!')
  cy.get('.wp-generate-pw').click() // Click "Set password" button if needed
  cy.get('#pass1').clear().type('TestPassword123!')
  
  cy.get('#role').select(role)
  cy.get('#createusersub').click()
  cy.url().should('include', 'users.php')
})
