Cypress.Commands.add('login', (username, password) => {
    cy.get('#username').type(username);
    cy.get('#password').type(password);
    cy.get('button[type="submit"]').click();
});
