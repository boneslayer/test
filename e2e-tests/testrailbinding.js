process.env.UV_THREADPOOL_SIZE = 128;

var config = require('./testrailbindingcfg.json');
// "url":"https://binagoratest.testrail.io",
// "username":"pablo@binagora.com",
// "email": "pablo@binagora.com",
// "password":"1IbcgeJ2tdodLy4hTGWo-zUyWDykrVq8f4vWlYF.W",

var TestRail = require("testrail-promise");
var tr = new TestRail(config.url, config.username, config.password);
tr.allowUntrustedCertificate();
tr.simpleRequests();

var statusIdByName = function(text){
  switch(text.toLowerCase()){
      case 'passed': return 1;
      case 'blocked': return 2;
      case 'untested': return 3;
      case 'retest': return 4;
      case 'failed': return 5;
  }
};

var obj = {
  "project_name":"Protractor",
  "plan_name":"Protractor Test"
}

 tr.getProjectIdByName(obj)
  .then(function(project_id) {
    obj.project_id = project_id;
    return tr.getPlanIdByName(obj).then(function(plan_id) {
      obj.plan_id = plan_id;
      return tr.getPlan(obj).then(getPlanData);
    })
    .catch(function(err) {
      console.log(err);
    })
  })
  .catch(function(err) {
    console.log(err);
  })

function getPlanData(plan) {
  var entries = plan.entries;

  for (var i = 0; i < entries.length; i++){
      var entry = entries[i];
      var runs = entry['runs'];
      // console.log("entry", i, entry);
      for (var j = 0; j < runs.length; j++) {
        var run = runs[j];
        // console.log("run", j, run);

        var runDetails = {
          "run_id": run['id'],
          "run_name": run['name'],
          "suite_id": run['suite_id'],
        };        
        tr.getTests(runDetails).then(callBackParam(runDetails));
      }
    }
  }
  function callBackParam(runDetails){
    return function(testData){
      runDetails.run_tests = testData;
      // console.log("runDetails", runDetails);
      processTest(runDetails);
    }  
}

const exec = require('child_process').exec;

function processTest(runDetails) {
  var file = runDetails.run_tests[0].custom_preconds;
  var runId = runDetails.run_tests[0].run_id;
  
  exec("protractor --specs=" + file + ".js,common_spec.js protractor.conf.js", function(e, stdout, stderr) {
    var runResult = require("./"+ file + ".output.json"); // [{"method1":"value"}, ... ]
    var results = new Array();
    
    console.log(file);
    
    runResult.forEach(function(eachResult) {
      runDetails.run_tests.forEach(function(runTestData) {
        
        if (eachResult.description == runTestData.title) {
          // console.log("eachResult",eachResult);
          // console.log("runtestdata",runTestData);
          
          results.push({
            "test_id": runTestData.id,
            "status_id": statusIdByName(eachResult.status_name),
            "comment": "test " + eachResult.status_name
          });
        }
      });
    });
    
    var sendObj = {
      "run_id": runId,
      "results": results
    };
    sendResult(sendObj);
  });
}

//(a,b) => {}  === function(a,b) {}
//a => a++ === a => { return a++; } === function(a) { return a++; }

function sendResult(sendObj) {
  tr.addResults(sendObj)
    .then(function(result) {
      console.log('result');
      console.log(result);
    })
    .catch(function(err) {
      console.log(err);
    })
}
