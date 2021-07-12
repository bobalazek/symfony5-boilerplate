
Cypress.Commands.add('login', (username = 'user', password = 'password') => {
  cy.visit('/auth/login');

  cy.get('input[name="username"]').type(username);
  cy.get('input[name="password"]').type(password);

  cy.get('button[type="submit"]').click();
});

Cypress.Commands.add('logout', () => {
  cy.visit('/auth/logout');
});

Cypress.Commands.add('getInputByLabel', (label) => {
  return cy.get('label').contains(label).parent().find('.form-control');
});
