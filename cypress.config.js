const { defineConfig } = require('cypress')
const fs = require('fs')
const path = require('path')

module.exports = defineConfig({
  e2e: {
    baseUrl: 'http://localhost:8888',
    viewportWidth: 1280,
    viewportHeight: 720,
    video: false,
    screenshotOnRunFailure: true,
    setupNodeEvents(on, config) {
      // Task to create mu-plugins for testing filters
      on('task', {
        createMuPlugin({ name, content }) {
          const muPluginsDir = path.join(__dirname, 'tests', 'mu-plugins')
          if (!fs.existsSync(muPluginsDir)) {
            fs.mkdirSync(muPluginsDir, { recursive: true })
          }
          const filePath = path.join(muPluginsDir, `${name}.php`)
          fs.writeFileSync(filePath, content)
          return null
        },
        
        deleteMuPlugin(name) {
          const filePath = path.join(__dirname, 'tests', 'mu-plugins', `${name}.php`)
          if (fs.existsSync(filePath)) {
            fs.unlinkSync(filePath)
          }
          return null
        },
        
        setEnvironmentType(envType) {
          const content = `<?php
define('WP_ENVIRONMENT_TYPE', '${envType}');`
          const muPluginsDir = path.join(__dirname, 'tests', 'mu-plugins')
          if (!fs.existsSync(muPluginsDir)) {
            fs.mkdirSync(muPluginsDir, { recursive: true })
          }
          const filePath = path.join(muPluginsDir, 'set-environment-type.php')
          fs.writeFileSync(filePath, content)
          return null
        },
      })
    },
  },
  env: {
    wpUsername: 'admin',
    wpPassword: 'password',
  },
})
