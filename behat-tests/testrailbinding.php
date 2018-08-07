<?php
header('Content-Type: text/html; charset=utf-8');

require 'testrail/testrail.php';

$client = null;
$config = null;

function init()
{
    global $client, $config;
    if (file_exists('testrail/testrailbinding.cfg')){
        $config = json_decode(file_get_contents('testrail/testrailbinding.cfg'),true);
        $client = new TestRailAPIClient($config['url']);
        $client->set_user($config['username']);
        $client->set_password($config['password']);
        //return $client;
    }else {
        print_r(PHP_EOL."ERROR GETTING CONFIG FILE".PHP_EOL);die();
    }
}

#get user id from email
function getUserIdFromEmail($email){
    global $client;
    $user = null;
    $users = $client->send_get('get_users/');
    foreach ($users as $user) {
        if ($user["email"] == $email){
            $userId = $user['id'];
            return $userId;
        }
    }
    return '';
}

#get projects
function getProjects(){
    global $client;
    $projects = null;
    $projects = $client->send_get('get_projects');
    return $projects;
}
#get project from name
function getProjectIdFromName($projects, $projectName){
    foreach ($projects as $project) {
        if ($project["name"] == $projectName){
            return $project["id"];
        }
    }
    return '';
}

###suites functions###
#get suites
function getSuites($projectId){
    global $client;
    $suites = null;
    $suites = $client->send_get('get_suites/'.$projectId);
    return $suites;
}
#get suite from Id
function getSuiteFromId($suiteId){
    global $client;
    $suite = null;
    $suite = $client->send_get('get_suite/'.$suiteId);
    return $suite;
}
#get suite from name
function getSuiteFromName($suites,$suiteName){
    foreach ($suites as $suite) {
        if ($suite["name"] == $suiteName){
            return $suite;
        }
    }
    return '';
}
#get sections from suite name
function getSectionsFromSuiteName($projectId, $suiteName){
    global $client;
    $sections = null;
    $suites = getSuites($projectId);
    $suite = getSuiteFromName($suites, $suiteName);
    $suiteId = $suite["id"];
    $sections = $client->send_get('get_sections/'.$projectId.'&suite_id='.$suiteId);
    return $sections;
}
#get test cases from suite id
function getTestCasesFromSuiteId($projectId, $suiteId){
    global $client;
    $cases = null;
    $cases = $client->send_get('get_cases/'.$projectId.'&suite_id='.$suiteId);
    return $cases;
}

###plans functions###
#get plans
function getPlans($projectId){
    global $client;
    $plans = null;
    $plans = $client->send_get('get_plans/'.$projectId);
    return $plans;
}
#get plan from Id
function getPlanFromId($planId){
    global $client;
    $plan = null;
    $plan = $client->send_get('get_plan/'.$planId);
    return $plan;
}
#get plan from name
function getPlanFromName($plans,$testPlanName){
    foreach ($plans as $reducedPlan) {
        if ($reducedPlan["name"] == $testPlanName){
            $plan = getPlanFromId($reducedPlan["id"]);
            return $plan;
        }
    }
    return '';
}


###runs functions###
#get runs
function getRuns($projectId){
    global $client;
    $runs = null;
    $runs = $client->send_get('get_runs/'.$projectId);
    return $runs;
}
#get run from Id
function getRunFromId($runId){
    global $client;
    $run = null;
    $run = $client->send_get('get_run/'.$runId);
    return $run;
}
#get run from name
function getRunFromName($runs,$testRunName){
    foreach ($runs as $run) {
        if ($run["name"] == $testRunName){
            return $run;
        }
    }
    return '';
}

#get tests from test runs of a plan
function getTestsFromPlanRuns($plan){
    global $client;
    $planRunsTests = array();

    foreach ($plan["entries"] as $entry) {
        foreach ($entry["runs"] as $run) {
            $planRunTests = [
                "runId" => $run["id"],
                "runName" => $run["name"],
                "suite_id" => $run["suite_id"],
                "runTests" => getTests($run["id"])
            ];
            $planRunsTests[] = $planRunTests;

        }
    }
    return $planRunsTests;
}
#get tests from test run
function getTests($runId){
    global $client;
    $tests = null;
    $tests = $client->send_get('get_tests/'.$runId);
    return $tests;
}

#get test case data
function getTestCase($testCaseId){
    global $client;
    $case = null;
    $case = $client->send_get('get_case/'.$testCaseId);
    return $case;
}

/**
* returns steps from tests converted into behat style
**/
function getTestStepsConverted($tests){
    global $config;
    $tab = '    ';
    $featureData = null;
    $module = '';

    foreach ($tests as $test) {
        $featureData .= '#case: '.$test['case_id'].' test: '.$test['id'].PHP_EOL;
        //$featureData .= $tab.$test["custom_automation_comments"].PHP_EOL;
        $featureData .= $tab.'Scenario: '.$test['title'].PHP_EOL;

        #use preconditions as first steps to initiate lui and nui sessions
        // $customPreconds = $test['custom_preconds'];
        // $customPreconds = preg_split("/\r\n|\r|\n/", $customPreconds);
        // foreach ($customPreconds as $customPrecond) {
        //     if ($customPrecond == "Module: LUI"){
        //         $featureData .= $tab.$tab.'Given I switch to session "LUI"'.PHP_EOL;
        //         $featureData .= $tab.$tab.'When I am on "'.$config['LUI'].'"'.PHP_EOL;
        //         $module = 'LUI';
        //     }
        //     if (($customPrecond == "Module: NUI") || ($customPrecond == "Module: OT")){
        //         $featureData .= $tab.$tab.'Given I switch to session "LUI"'.PHP_EOL;
        //         $featureData .= $tab.$tab.'When I am on "'.$config['LUI'].'"'.PHP_EOL;
        //         $featureData .= $tab.$tab.'And I switch to session "NUI"'.PHP_EOL;
        //         $featureData .= $tab.$tab.'And I am on "'.$config['NUI'].'"'.PHP_EOL;
        //         $module = 'NUI';
        //     }
        //     if ($customPrecond == "LUI BigButtonUI is in 'Auto'"){
        //         $featureData .= $tab.$tab."When I press 'Auto' button in 'LUI' BigButtonUI".PHP_EOL;
        //     }
        //     if ($customPrecond == "NUI BigButtonUI is in 'Auto'"){
        //         $featureData .= $tab.$tab."When I press 'Auto' button in 'NUI' BigButtonUI".PHP_EOL;
        //     }
        // }
        // var_dump($customPreconds);die();

        $steps = $test["custom_steps_separated"];
        foreach ($steps as $step) {
            if (empty($step['content'])){
                $featureData .= $tab.$tab."And undefined step".PHP_EOL;
            }else {
              //  $step['content'] = str_replace('\\\'','[SQ]',$step['content']);

                // if((strpos($step['content'], 'UI reaction of')) && ($module == 'LUI')){
                    // $stepCont = addcslashes(preg_replace("/\r\n|\r|\n/", ';', $step['content']), '"');
                // }else{
                //    $stepCont = addcslashes(preg_replace("/\s+/", ' ', $step['content']), '"');
                // }
              //  $featureData .= $tab.$tab.$stepCont.PHP_EOL;
                $featureData .= $tab.$tab.$step['content'].PHP_EOL;
            }
            if (empty($step['expected'])){
                $featureData .= $tab.$tab."And undefined expected".PHP_EOL;
            }else {
              //  $step['expected'] = str_replace('\\\'','[SQ]',$step['expected']);

                // if((strpos($step['expected'], 'UI reaction of')) && ($module == 'LUI')){
                    // $stepExp = addcslashes(preg_replace("/\r\n|\r|\n/", ';', $step['expected']), '"');
                // }else{
              //      $stepExp = addcslashes(preg_replace("/\s+/", ' ', $step['expected']), '"');
                // }
            //  $featureData .= $tab.$tab.$stepExp.PHP_EOL;
                $featureData .= $tab.$tab.$step['expected'].PHP_EOL;
            }
        }
    }
    return $featureData;
}

/**
* generate feature files from the test cases of a test run
*/
function generateFeatureFromTestRunCases($projectId, $testRunName){
    $tab = '    ';
    $featureData = null;

    $runs = getRuns($projectId);
    $run = getRunFromName($runs, $testRunName);

    $runId = $run["id"];
    $runSuiteId = $run["suite_id"];

    $tests = getTests($runId);

    //$suites = getSuites($projectId);
    $suite = getSuiteFromId($runSuiteId);
    $suiteDesc = $suite["description"];
    $suiteName = $suite["name"];

    #feature header
    $featureData .= 'Feature: '.$suiteName.PHP_EOL;
    $featureData .= $tab.$suiteDesc.PHP_EOL.PHP_EOL;

    $featureData .= getTestStepsConverted($tests);
    print_r($featureData);

    $featureFileName = 'integration/testrail/testrail_run_'.$run['id'].'.feature';
    file_put_contents($featureFileName, $featureData);

    return $featureFileName;
}

/**
* generate feature files from the test cases of every test run of a test plan
*/
function generateFeatureFromTestPlanTestRunsCases($projectId, $testPlanName){
    $featuresFileName = array();
    $tab = '    ';
    $featureData = "";

	$plans = getPlans($projectId);
    $plan = getPlanFromName($plans, $testPlanName);
    $planRunsTests = getTestsFromPlanRuns($plan);

    foreach ($planRunsTests as $planRunTests) {
        $suite = getSuiteFromId($planRunTests["suite_id"]);
        $suiteDesc = $suite["description"];
        $suiteName = $suite["name"];

        #feature header
        $featureData = 'Feature: '.$suiteName.PHP_EOL;
        $featureData .= $tab.$suiteDesc.PHP_EOL.PHP_EOL;

        $featureData .= getTestStepsConverted($planRunTests["runTests"]);
        print_r($featureData);

        $featureFileName = 'integration/testrail/testrail_plan_'.$plan['id'].'_run_'.$planRunTests['runId'].'.feature';
        file_put_contents($featureFileName, $featureData);
        $featuresFileName[] = $featureFileName;
    }#foreach
    return $featuresFileName;
}

function executeFeatures(){
    $command = 'php vendor/behat/behat/bin/behat integration/testrail';
    echo 'command: '.$command.PHP_EOL;
    echo 'Excecuting all features: '.PHP_EOL;
    $output = system($command);
    echo $output;
}

function executeFeaturesFromPlan($projectId, $testPlanName){
    global $client, $config;

    $userId = getUserIdFromEmail($config['email']);
    $plans = getPlans($projectId);
    $plan = getPlanFromName($plans, $testPlanName);
    $planRunsTests = getTestsFromPlanRuns($plan);

    foreach ($planRunsTests as $planRunTests) {
        $featureFileName = 'integration/testrail/testrail_plan_'.$plan['id'].'_run_'.$planRunTests['runId'].'.feature';
        $command = 'php vendor/behat/behat/bin/behat '.$featureFileName;

        echo 'command: '.$command.PHP_EOL;
        echo 'Excecuting all features from test plan "'.$testPlanName.'": '.PHP_EOL;
        echo 'feature from test Run "'.$planRunTests['runId'].'": '.PHP_EOL;
        $output = system($command);
        echo $output;
    }
}

function executeFeatureFromRun($projectId, $testRunName){
    $runs = getRuns($projectId);
    $run = getRunFromName($runs, $testRunName);
    $runId = $run["id"];

    $featureFileName = 'integration/testrail/testrail_run_'.$runId.'.feature';
    $command = 'php vendor/behat/behat/bin/behat '.$featureFileName;

    echo 'command: '.$command.PHP_EOL;
    echo 'Excecuting the feature from test Run "'.$testRunName.'": '.PHP_EOL;
    $output = system($command);
    echo $output;
}

/**
* Send back the result of every test run of a test plan execution to testrail
**/
function setResultForTestPlanRuns($projectId, $testPlanName){
    global $client, $config;

    $userId = getUserIdFromEmail($config['email']);
    $plans = getPlans($projectId);
    $plan = getPlanFromName($plans, $testPlanName);
    $planRunsTests = getTestsFromPlanRuns($plan);

    foreach ($planRunsTests as $planRunTests) {
        $resultFile = "testrail/cache/testrail_plan_".$plan['id']."_run_".$planRunTests['runId'].".log";
        $scenariosResult = unserialize(file_get_contents($resultFile));

        $runResult = setResultForTestRunId($planRunTests['runId'], $planRunTests["runTests"], $scenariosResult, $userId);
        $result[] = $runResult;
    }
    return $result;
}

/**
* Send back the result of the test run (by Name) execution to testrail
**/
function setResultForTestRun($projectId, $testRunName){
    global $client, $config;

    $userId = getUserIdFromEmail($config['email']);
    $runs = getRuns($projectId);
    $run = getRunFromName($runs, $testRunName);
    $runId = $run["id"];
    $tests = getTests($runId);

    $resultFile = "testrail/cache/testrail_run_".$runId.".log";
    $scenariosResult = unserialize(file_get_contents($resultFile));

    $result = setResultForTestRunId($runId, $tests, $scenariosResult, $userId);

    return $result;
}

/**
* Send back the result of the test run (by Id) execution to testrail
**/
function setResultForTestRunId($runId, $tests, $scenariosResult, $userId){
    global $client, $config;

    foreach ($tests as $test) {
        $caseId = $test['case_id'];
        $caseName = $test['title'];

        foreach ($scenariosResult as $scenarioResult) {
            if ($scenarioResult['scenarioName'] == $caseName){
                switch ($scenarioResult['resultCode']) {
                    case '00':
                        $statusId = 1;
                        $comment = 'Automated Scenario Test Passed.';
                        break;
                    case '99':
                        $statusId = 5;
                        $comment = 'Automated Scenario Test Failed.';
                        break;
                }
                $custom_step_results = array();

                $steps = $scenarioResult['steps'];
                $count = 1;
                foreach ($steps as $step) {
                    switch ($step['stepStatus']) {
                        case 'passed':
                            $stepStatus = 1;
                            break;
                        case 'failed':
                            $stepStatus = 5;
                            break;
                        case 'pending':
                            $stepStatus = 3;
                            break;
                    }
                    if ($step['stepName'] != 'Given I am on "\"'){
                        if ($count & 1){
                            if (($step['stepName'] != 'And undefined step') && ($step['stepName'] != 'And undefined expected')){
                                $stepName = $step['stepName'];
                                $stepName = stripslashes($stepName);
                            // if ($stepStatus == 1){
                                // continue;
                            // }
                            }else {
                                $stepName = '';
                            }
                        }else {
                            if (($step['stepName'] != 'And undefined step') && ($step['stepName'] != 'And undefined expected')){
                                $stepExpected = $step['stepName'];
                                $stepExpected = stripslashes($stepExpected);
                            }else {
                                $stepExpected = '';
                            }
                            $custom_step_result = [
                                "status_id" => $stepStatus,
                                "content" => $stepName,
                                "expected" => $stepExpected,
                            ];
                            $custom_step_results[] = $custom_step_result;

                        }
                        // $stepName = nl2br(str_replace(',',",\n",stripslashes($stepName)));
                        // $stepExpected = nl2br(str_replace(',',",\n",stripslashes($stepExpected)));

                        if (count($steps) == $count){
                            $stepExpected = '';
                            $custom_step_result = [
                                "status_id" => $stepStatus,
                                "content" => $stepName,
                                "expected" => $stepExpected,
                            ];
                            $custom_step_results[] = $custom_step_result;
                        }
                        $count++;
                    }
                }

                $result = $client->send_post(
                    'add_result_for_case/'.$runId.'/'.$caseId,
                    array(
                        'status_id' => $statusId,
                        'comment' => $comment,
                        'assignedto_id' => $userId,
                        'custom_step_results' => $custom_step_results
                    )
                );
                break;
            }
        }
    }
    return $result;
}

/************************************************************/
init();

$projects = getProjects();
$projectId = getProjectIdFromName($projects, $config['project']);
$testPlanOrRunName = $argv[4];
$featuresFileName = array();
$featureFileName = '';

if ($argv[1] == 'generatePlan'){
    $featuresFileName = generateFeatureFromTestPlanTestRunsCases($projectId, $testPlanOrRunName);
}else if($argv[1] == 'generateRun') {
    $featureFileName = generateFeatureFromTestRunCases($projectId, $testPlanOrRunName);
}

if ($argv[2] == 'executePlan'){
    executeFeaturesFromPlan($projectId, $testPlanOrRunName);
}else if ($argv[2] == 'executeRun'){
    executeFeatureFromRun($projectId, $testPlanOrRunName);
}else if ($argv[2] == 'executeAll'){
    executeFeatures();
}

if ($argv[3] == 'sendRunResult'){
    $result = setResultForTestRun($projectId, $testPlanOrRunName);
    var_dump($result);
}else if ($argv[3] == 'sendPlanResult'){
    $result = setResultForTestPlanRuns($projectId, $testPlanOrRunName);
    var_dump($result);
}
