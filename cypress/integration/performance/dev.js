// Extract globals.
const {_, $} = Cypress;

// Get environment sim.
const envsim = require('../../../environment-sim.json');

// Load endpoints.
const endpoints = require('../../fixtures/endpoints.json');

// Initialize the test.
describe('Performance on dev', () => {
  
  // Test each route.
  endpoints['data'].forEach((endpoint) => {
    
    // Get the endpoint's URL and URI.
    const uri = _.isArray(endpoint.endpoint) ? endpoint.endpoint[0] : endpoint.endpoint;
    const url = `dev.${envsim.site}${uri}`;
    
    // Configure tests for the current the route.
    describe(uri, () => {
      
      // Test that the route can be reached and returns the appropriate response code.
      it('Returns the appropriate response code', () => {
        
        // Request the route.
        cy.request({
          url,
          failOnStatusCode: false
        }).then((response) => {
          
          // For error endpoints, their response code should match its error code.
          if( endpoint.error !== false ) expect(response.status).to.equal(+endpoint.error);
          
          // For asset endpoints, their response code should be 200.
          else if( endpoint.asset === true ) expect(response.status).to.equal(200);
          
          // For redirect endpoints, their response code should either be either 515 or 200.
          else if( endpoint.redirect !== false ) expect(response.status).to.be.oneOf([200, 515]);
          
          // For endpoints without a template, the response code should be 515.
          else if( endpoint.pattern === null ) expect(response.status).to.equal(515);
          
          // Otherwise, for endpoints with templates, the response code should be 200.
          else expect(response.status).to.equal(200);
          
        });

      });
      
      // Then, test that the route loads in an acceptable amount of time.
      it('Loads in an acceptable amount of time', () => {
        
        // Load configuration data.
        cy.fixture('config').then((config) => {
          
          // Get maximum page load times.
          const {maxPageLoadTime} = config;
        
          // For asset routes, use the request method.
          if( endpoint.asset === true ) {

            // Visit the route.
            cy.request({
              url,
              failOnStatusCode: false
            }).then((response) => {

              // Get the asset's elapsed load time, and ensure that it didn't exceed the expected limit.
              expect(response.duration).to.be.at.most(maxPageLoadTime.assets);

            });

          }

          // Otherwise, for non-asset routes, use the visit method.
          else {

            // Initialize the load time.
            let time;

            // Visit the route.
            cy.visit({
              url,
              failOnStatusCode: false,
              onLoad: (window) => {
                
                // Capture the load time.
               time = window.performance.timing.loadEventEnd - window.performance.timing.navigationStart;
                
              }
            }).then(() => {

              // Get the page's elapsed load time, and ensure that it didn't exceed the expected limit.
              expect(time).to.be.at.most(maxPageLoadTime.pages);

            });

          }
          
        });
        
      });
      
    });
    
  });
  
});