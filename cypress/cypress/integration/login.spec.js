/// <reference types="cypress" />

describe('Login', () => {
  before(() => {
    cy.task('database:recreate');
  });

  it('Checking if a valid login works', () => {
    cy.visit('/auth/login');

    cy.get('h1').contains('Login');

    cy.get('#username-input').type('user');
    cy.get('#password-input').type('password');

    cy.get('button[type="submit"]').click();

    cy.get('h1').contains('home.heading');
  });

  it('Checking if a invalid login returns an error for existing user', () => {
    cy.visit('/auth/login');

    cy.get('h1').contains('Login');

    cy.get('#username-input').type('user');
    cy.get('#password-input').type('definetlythewrongpassword');

    cy.get('button[type="submit"]').click();

    cy.get('.alert.alert-danger').contains('Invalid credentials');
  });

  it('Checking if a invalid login returns an error for non-existing user', () => {
    cy.visit('/auth/login');

    cy.get('h1').contains('Login');

    cy.get('#username-input').type('non-existing-user');
    cy.get('#password-input').type('definetlythewrongpassword');

    cy.get('button[type="submit"]').click();

    cy.get('.alert.alert-danger').contains('could not be found');
  });
});
