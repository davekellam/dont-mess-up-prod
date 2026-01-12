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
 * Custom command to check if environment indicator exists with correct environment
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
