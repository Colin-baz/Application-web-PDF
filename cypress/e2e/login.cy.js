describe('Formulaire de Connexion', () => {
    it('test 1 - connexion OK', () => {
        cy.visit('/login');

        cy.get('#username').type('aerocol352@gmail.com');
        cy.get('#password').type('Colinba1373');

        cy.get('button[type="submit"]').click();

        cy.contains('Bonjour Colin !').should('exist');
    });

    it('test 2 - connexion KO', () => {
        cy.visit('/login');

        cy.get('#username').type('aerocol352@gmail.com');
        cy.get('#password').type('12345');

        cy.get('button[type="submit"]').click();

        cy.contains('Email ou mot de passe incorrect').should('exist');
    });
});
