/// <reference types="cypress" />

describe('Homepage', () => {
  it('Checking if the homepage works', () => {
      cy.visit('/');

      cy.get('h1').contains('home.heading');
  });

  it.only('Checking if footer language dropdown works', () => {
    cy.visit('/');

    cy.get('.page-footer .dropdown .dropdown-toggle').contains('English').click();

    cy.get('.page-footer .dropdown .dropdown-menu').contains('Deutsch').click();

    cy.get('.page-footer .dropdown .dropdown-toggle').contains('Deutsch').should('exist');
  });
});
