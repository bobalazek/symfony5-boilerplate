/// <reference types="cypress" />

const axios = require('axios');

/**
 * @type {Cypress.PluginConfig}
 */
module.exports = (on, config) => {
  on('task', {
    async 'database:recreate'() {
      try {
        await axios.get(`${config.baseUrl}/dev?action=database_recreate`);
        return true;
      } catch (error) {
        console.log(error);
        return false;
      }
    },
  });

  return config;
}
