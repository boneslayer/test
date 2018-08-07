'use strict';


// Angular E2E Testing Guide:
// https://docs.angularjs.org/guide/e2e-testing

describe('View: Phone detail', function() {
  

  beforeEach(function() {
    browser.get('index.html#!/phones/nexus-s');
  });

  it('should display the `nexus-s` page', function() {
    expect(element(by.binding('$ctrl.phone.name')).getText()).toBe('Nexus S');
  });

  it('should display the first phone image as the main phone image', function() {
    var mainImage = element(by.css('img.phone.selected'));

    expect(mainImage.getAttribute('src')).toMatch(/img\/phones\/nexus-s.0.jpg/);
  });

  it('should swap the main image when clicking on a thumbnail image', function() {
    var mainImage = element(by.css('img.phone.selected'));
    var thumbnails = element.all(by.css('.phone-thumbs img'));

    thumbnails.get(2).click();
    expect(mainImage.getAttribute('src')).toMatch(/img\/phones\/nexus-s.2.jpg/);

    thumbnails.get(0).click();
    expect(mainImage.getAttribute('src')).toMatch(/img\/phones\/nexus-s.0.jpg/);
  });

  
});
