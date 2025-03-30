module.exports = {
  e2e: {
    baseUrl: 'http://127.0.0.1:8319/',
    specPattern: [
      'cypress/e2e/login.cy.js'
    ],
    supportFile: 'cypress/support/commands.js',
    viewportWidth: 1280,
    viewportHeight: 720,
  },
};
