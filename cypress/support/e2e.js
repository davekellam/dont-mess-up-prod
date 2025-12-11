// Import commands
import './commands'

// Prevent Cypress from failing on uncaught exceptions
Cypress.on('uncaught:exception', (err, runnable) => {
  // Returning false here prevents Cypress from failing the test
  // WordPress admin sometimes has benign JS errors
  return false
})
