/// <reference types="cypress" />

describe('Signup', () => {
  before(() => {
    cy.task('database:recreate');
  });

  it('Checking if the signup works', () => {
      cy.visit('/auth/register');

      cy.get('h1').contains('Signup');

      cy.get('input[name="register[firstName]"]').type('John');
      cy.get('input[name="register[lastName]"]').type('Doe');
      cy.get('input[name="register[username]"]').type('johndoe');
      cy.get('input[name="register[email]"]').type('johndoe@johndoe.com');
      cy.get('input[name="register[plainPassword][first]"]').type('super123complex456password');
      cy.get('input[name="register[plainPassword][second]"]').type('super123complex456password');
      cy.get('input[name="register[termsAgree]"]').check();
      cy.get('button[type="submit"]').click();

      cy.get('div.alert.alert-success').contains('successfully');
  });
});
