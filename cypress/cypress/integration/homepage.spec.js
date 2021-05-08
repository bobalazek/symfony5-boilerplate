/// <reference types="cypress" />

describe('Homepage', () => {
  it('Checking if the homepage works', () => {
      cy.visit('/');

      cy.get('h1').contains('home.heading');
  });
});
