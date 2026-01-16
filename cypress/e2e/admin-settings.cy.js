describe("Admin Settings - Environment Configuration", () => {
  beforeEach(() => {
    cy.wpLogin()
    cy.visit("/wp-admin/options-general.php?page=dont-mess-up-prod")
  })

  it("should load the settings page", () => {
    cy.get("h1").should("contain.text", "Don't Mess Up Prod")
    cy.contains("Environment Configuration").should("be.visible")
  })

  it("should render color and URL fields for each environment", () => {
    const environments = ["local", "development", "staging", "production"]

    environments.forEach((env) => {
      cy.get(`#dmup_color_${env}`).should("exist")
      cy.get(`#dmup_url_${env}`).should("exist")
    })
  })

  it("should show the Save Settings button", () => {
    cy.contains('input[type="submit"], button', "Save Settings").should("be.visible")
  })
})
