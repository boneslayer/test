
//Common to every spec file
var fs = require('fs');
var path = require("path");

var logFilename;

var resultLeaker = {
  suiteStarted: function(result){ jasmine.results = {suite:result}; },
  specStarted: function(result){ jasmine.results.spec = result; }
};
jasmine.getEnv().addReporter(resultLeaker);

var resultArr = new Array();

jasmine.getEnv().topSuite().beforeAll({fn: function() {
    browser.getProcessedConfig().then(function(config){
        logFilename = path.basename(config.specs[0]).slice(0,-3)+".output.json";
        fs.unlink(logFilename, function(err) {
            if(err && err.code == 'ENOENT') {
                // file doens't exist
                console.info("File doesn't exist, won't remove it.");
            } else if (err) {
                // other errors, e.g. maybe we don't have enough permission
                console.error("Error occurred while trying to remove file");
            } else {
                console.info(`removed`);
            }
        });
    })
    
}});

jasmine.getEnv().topSuite().afterEach({fn: function() {
  var result ={
    "id": jasmine.results.spec.id,
    "description":jasmine.results.spec.description,
    "fullName":jasmine.results.spec.fullName,
    "failedExpectations":jasmine.results.spec.failedExpectations,
    "passedExpectations":jasmine.results.spec.passedExpectations,
    "pendingReason":jasmine.results.spec.pendingReason,
    "status_name":(jasmine.results.spec.failedExpectations.length === 0 ? "passed" : "failed")
  };
  resultArr.push(result);
}});

jasmine.getEnv().topSuite().afterAll({fn: function() {  
    var resultFile = fs.createWriteStream(logFilename, {'flags': 'a'});
  // use {'flags': 'a'} to append and {'flags': 'w'} to erase and write a new file
  resultFile.write(JSON.stringify(resultArr, null, 2));
  // resultFile.end(",\n");
}});

