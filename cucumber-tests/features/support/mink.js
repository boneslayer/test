const cucumber = require('cucumber');
const mink = require('cucumber-mink');

const driver = new mink.Mink({
 baseUrl: 'http://localhost:8000',
  viewport: {
    width: 1366,
    height: 768,
  },
});

// var parameters = {
  // driver: {
    // desiredCapabilities: {
      // browserName: 'chrome'
    // },
    // logLevel: 'silent',
    // port: 9515
  // }
// };

// module.exports = function () {
  // Mink.init(this, parameters);
// };

driver.hook(cucumber);