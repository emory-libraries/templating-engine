// Extract globals.
const {_, $} = Cypress;



// Load routes and patterns.
const routes = require('../../fixtures/routes.json');
const patterns = require('../../fixtures/patterns.json');
const site = require('../../fixtures/site.json');

// Initialize the test.
describe('Performance on localhost', () => {
  
  // Test each route.
  routes['data'].forEach((route) => {
    
    // Get the endpoint, and URL.
    const endpoint = _.isArray(route.endpoint) ? route.endpoint[0] : route.endpoint;
    const url = _.isArray(route.url) ? route.url[0] : route.url;
    
    // Configure tests for the current the route.
    describe(endpoint, () => {
      
      // Test that the route can be reached and returns the appropriate response code.
      it('Returns the appropriate response code', () => {
        
        // Request the route.
        cy.request({
          url,
          failOnStatusCode: false
        }).then((response) => {
          
          // For error routes, their response code should match its ID.
          if( route.error ) expect(response.status).to.equal(+route.id);
          
          // For asset routes, their response code should be 200.
          else if( route.asset ) expect(response.status).to.equal(200);
          
          // Otherwise, for all other requests, the response code should be based on whether or not the page's template exists.
          else {
            
            // Get the route's page data.
            const page = site.data.site[route.path];
            
            // For routes with real page data, identify the exact response code that should be given.
            if( page ) {
            
              // Get the route's template based on its page type.
              const template = _.get(_.filter(patterns.data.templates, function(template) {

                // Find the template with a matching page type, PLID, or ID.
                return (template.pageType == page.data.template || template.plid == page.data.template || template.id == page.data.template);

              }), 0, false);

              // For routes with an existing template, the response code should be 200.
              if( template ) expect(response.status).to.equal(200);

              // Otherwise, for routes without an existing template, the response code should be 515.
              else expect(response.status).to.be.equal(515);
              
            }
            
            // Otherwise, for routes without any real data (e.g., predefined routes), permit a response code of either 200 or 515.
            else expect(response.status).to.be.oneOf([200, 515]);
            
          }
          
        });

      });
      
      // Then, test that the route loads in an acceptable amount of time.
      it('Loads in an acceptable amount of time', () => {
        
        // Load configuration data.
        cy.fixture('config').then((config) => {
          
          // Get maximum page load times.
          const {maxPageLoadTime} = config;
        
          // For asset routes, use the request method.
          if( route.asset ) {

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