/// <reference types="cypress" />

describe('Login', () => {
  before(() => {
    cy.task('database:recreate');
  });

  it('Checking if a valid login works', () => {
    cy.visit('/auth/login', {
      failOnStatusCode: false,
    });

    cy.get('h1').contains('Login');

    cy.get('input[name="username"]').type('user');
    cy.get('input[name="password"]').type('password');

    cy.get('button[type="submit"]').click();

    cy.get('h1').contains('home.heading');
  });

  it('Checking if a invalid login returns an error for existing user', () => {
    cy.visit('/auth/login');

    cy.get('h1').contains('Login');

    cy.get('input[name="username"]').type('user');
    cy.get('input[name="password"]').type('definetlythewrongpassword');

    cy.get('button[type="submit"]').click();

    cy.get('.alert.alert-danger').should('exist');
  });

  it('Checking if a invalid login returns an error for non-existing user', () => {
    cy.visit('/auth/login');

    cy.get('h1').contains('Login');

    cy.get('input[name="username"]').type('non-existing-user');
    cy.get('input[name="password"]').type('definetlythewrongpassword');

    cy.get('button[type="submit"]').click();

    cy.get('.alert.alert-danger').should('exist');
  });
});
