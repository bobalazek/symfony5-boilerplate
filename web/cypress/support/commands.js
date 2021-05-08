
Cypress.Commands.add('login', (username = 'user', password = 'password') => {
  cy.visit('/auth/login');

  cy.get('#username-input').type(username);
  cy.get('#password-input').type(password);

  cy.get('button[type="submit"]').click();

  cy.visit('/');
})
