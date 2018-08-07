<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Behat\Behat\Hook\Scope\BeforeStepScope;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;
//use Behat\Testwork\Hook\Scope\BeforeFeatureScope;

use Behat\Behat\Hook\Scope\AfterScenarioScope;


/**
 * Defines application features from the specific context.
 */
class CommonContext extends MinkContext implements Context, SnippetAcceptingContext
{
    public $mySqlServer;
    public $mySqlUser;
    public $mySqlPass;
    public $mySqlDatabase;
    private $resultFile;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct($mySqlServer, $mySqlUser, $mySqlPass, $mySqlDatabase)
    {
        $this->mySqlServer = $mySqlServer;
        $this->mySqlUser = $mySqlUser;
        $this->mySqlPass = $mySqlPass;
        $this->mySqlDatabase = $mySqlDatabase;
    }


    /**
     * @Given undefined expected
     */
    public function undefinedExpected()
    {
        //EMPY STEP EXPECTED
    }
    /**
     * @Given undefined step
     */
    public function undefinedStep()
    {
        //EMPY STEP
    }

    /**
    * @BeforeSuite
    */
    public static function beforeSuite(BeforeSuiteScope $scope)
    {
        exec("sudo pkill -f chrome[^.][^driver]");
    }

    /**
    * @BeforeFeature
    */
    public static function beforeFeature(BeforeFeatureScope $scope)
    {
        global $resultFile;
        $featurePath = $scope->getFeature()->getFile();
        $resultFile = "testrail/cache/".basename($featurePath,".feature").".log";

        if (file_exists($resultFile)){
            rename($resultFile, "testrail/cache/".basename($resultFile,".log")."_time_".date('m-d-Y--H-i-s--u').".log");
        }
    }


    /**
    * @AfterStep
    */
    public function takeScreenShotAfterFailedStep(AfterStepScope $scope)
    {
        $basePath = 'build_reports/behat/';
        if (99 === $scope->getTestResult()->getResultCode()) {
            $driver = $this->getSession()->getDriver();
            if (!($driver instanceof Selenium2Driver)) {
                return;
            }
            $fileName = "failed-test-".date('m-d-Y--H-i-s--u').".png";
            file_put_contents($basePath . $fileName, $this->getSession()->getDriver()->getScreenshot());
            file_put_contents($basePath . 'failed.html', '<a href="'.$fileName.'">'.$scope->getFeature()->getTitle().' - '.$scope->getStep()->getText().':'.$scope->getStep()->getLine()."</a><br/>\n", FILE_APPEND | LOCK_EX);
        }
    }

    /**
    * @AfterStep
    */
    public function writeStepsResult(AfterStepScope $scope){
        if (file_exists('testrail/cache/stepsResult.log')){
            $stepsResult = unserialize(file_get_contents('testrail/cache/stepsResult.log'));
        }else {
            $stepsResult = array();
        }
        //$resultCode = $scope->getTestResult()->getResultCode();
        $stepKeyword = $scope->getStep()->getKeyword();
        $stepText = $scope->getStep()->getText();
        $stepName = $stepKeyword.' '.$stepText;

        if ($stepName != 'And undefined expected'){
            // var_dump($stepName);
            // echo "scope\n";
            // var_dump($scope);
            $callResult = $scope->getTestResult()->getCallresult();

            if ($callResult->hasException() && $callResult->getException() instanceof PendingException) {
                $stepStatus = 'pending';
            }elseif ($callResult->hasException()) {
                $stepStatus = 'failed';
            }else {
                $stepStatus = 'passed';
            }

            $stepResult = [
                "stepStatus" => $stepStatus,
                "stepName" => $stepName,
            ];
            $stepsResult[] = $stepResult;
            $stepsResultSerialized = serialize($stepsResult);

            file_put_contents('testrail/cache/stepsResult.log', print_r($stepsResultSerialized, true).PHP_EOL);
        }
    }

    /**
    * @AfterScenario
    */
    public function writeScenariosResult(AfterScenarioScope $scope){
        global $resultFile;
        if (file_exists($resultFile)){
            $scenariosResult = unserialize(file_get_contents($resultFile));
        }else {
            $scenariosResult = array();
        }

        if (file_exists('testrail/cache/stepsResult.log')){
            $steps = unserialize(file_get_contents('testrail/cache/stepsResult.log'));
            unlink('testrail/cache/stepsResult.log');
        }else {
            $steps = array();
        }

        $resultCode = $scope->getTestResult()->getResultCode();
        $scenarioName = $scope->getScenario()->getTitle();

        $scenarioResult = [
            "resultCode" => $resultCode,
            "scenarioName" => $scenarioName,
            "steps" => $steps,
        ];
        $scenariosResult[] = $scenarioResult;
        $scenariosResultSerialized = serialize($scenariosResult);

        file_put_contents($resultFile, print_r($scenariosResultSerialized, true).PHP_EOL);
        // file_put_contents($resultFile.".NOSR", print_r($scenariosResult, true).PHP_EOL);
    }


    /**
     * @When I switch to session :arg1
     */
    public function iSwitchToSession($arg1)
    {
        $this->getMink()->setDefaultSessionName($arg1);
    }

    /**
     * @Then I expect the value :arg1 in the column :arg2 for the query :arg3
     */
    public function iExpectTheValueInTheColumnForTheQuery($value, $column, $query)
    {
        $conn = new mysqli($this->mySqlServer, $this->mySqlUser, $this->mySqlPass, $this->mySqlDatabase);
        if ($conn->connect_error) {
            die('Connection failed: ' . $conn->connect_error);
        }

        $result = $conn->query($query);
        $row = $result->fetch_assoc();
        $conn->close();

        if ($row[$column] != $value) {
            throw new Exception("The value '$value' was not found in the column '$column', instead '$row[$column]' was found for the query '$query'");
        }
    }

    /**
     * @Given /^I wait "([^"]*)" milliseconds$/
     */
    public function iWaitMilliseconds($arg1)
    {
        $this->getSession()->wait(
            $arg1
        );
    }

    /**
     * @Then I wait until I see :arg1 in the :arg2 element
     */
    public function iWaitUntilISeeInTheElement($arg1, $arg2)
    {

//        var_dump("$('".$arg2."').text() == '".$arg1."'");die();

        $this->getSession()->wait(
            10000,
            //"$('".$arg2.":contains(".$arg1.")').length !=0"
            "$('".$arg2."').text().indexOf('".$arg1."') > -1"
        );
        $jsEval = $this->getSession()->evaluateScript(
            //"return ($('".$arg2.":contains(".$arg1.")').length !=0);"
            "$('".$arg2."').text().indexOf('".$arg1."') > -1"
        );
        if (!$jsEval) {
            $message = 'The element didn\'t contain the expected string';
            throw new Exception($message);
        }
    }

    /**
     * @Then I wait until I not see :arg1 in the :arg2 element
     */
    public function iWaitUntilINotSeeInTheElement($arg1, $arg2)
    {
        $this->getSession()->wait(
            10000,
            "$('".$arg2.":contains(".$arg1.")').length ==0"
        );
        $jsEval = $this->getSession()->evaluateScript(
            "return ($('".$arg2.":contains(".$arg1.")').length ==0);"
        );
        if (!$jsEval) {
            $message = 'The element still contains the unexpected string';
            throw new Exception($message);
        }
    }

    /**
      * @When I wait until I see :arg1 element
      */
    public function iWaitUntilISeeElement($arg1)
    {
        $this->getSession()->wait(
            10000,
            "$('".$arg1."').length !=0"
        );
        $jsEval = $this->getSession()->evaluateScript(
            "return ($('".$arg1."').length !=0);"
        );
        if (!$jsEval) {
            $message = 'The element didn\'t appeared';
            throw new Exception($message);
        }
    }

    /**
      * @When I wait until I not see :arg1 element
      */
    public function iWaitUntilINotSeeElement($arg1)
    {
        $this->getSession()->wait(
            10000,
            "$('".$arg1."').length ==0"
        );
        $jsEval = $this->getSession()->evaluateScript(
            "return ($('".$arg1."').length ==0);"
        );
        if (!$jsEval) {
            $message = 'The element is still present';
            throw new Exception($message);
        }
    }

    /**
     * @Given /^I wait to be logged in$/
     */
    public function iWaitToBeLoggedIn()
    {
        //wait until home page is loaded
        $this->getSession()->wait(
            10000,
            "$('a[href$=\"logout\"]').length;"
        );
        $jsEval = $this->getSession()->evaluateScript(
            "return $('a[href$=\"logout\"]').length;"
        );
        if (!$jsEval) {
            $message = 'Cannot log in';
            throw new Exception($message);
        }

    }

    /**
     * @when /^(?:|I )confirm the popup$/
     */
    public function confirmPopup()
    {
        $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
    }

    /**
     * @Then I should only see one result in the table :arg1
     */
    public function iShouldOnlySeeOneResultInTheTable($arg1)
    {
        $jsEval = $this->getSession()->evaluateScript(
            "$('" . $arg1 . " tbody tr').length == 1
            "
        );
        if (!$jsEval) {
            $message = 'There should be only one result after search in the table';
            throw new Exception($message);
        }
    }

    /**
     * @Then I should only see :arg2 results in the table :arg1
     */
    public function iShouldOnlySeeResultsInTheTable($arg1,$arg2)
    {
        $jsEval = $this->getSession()->evaluateScript(
            "$('" . $arg1 . " tbody tr').length == ".$arg2."
            "
        );
        if (!$jsEval) {
            $message = 'The table contains different results than expected';
            throw new Exception($message);
        }
    }

    /**
     * @When I fill and keyup in :arg1 with :arg2
     */
    public function iFillAndKeyupInWith($arg1, $arg2)
    {
        $findById = $this->getSession()->evaluateScript(
            "return ($('#".$arg1."').length !=0);"
        );
        $findByName = $this->getSession()->evaluateScript(
            "return ($('input[name=".$arg1."]').length !=0);"
        );
        if (!$findById && !$findByName) {
            $message = 'Filter Not Found';
            throw new Exception($message);
        } else {
            if ($findById) {
                $this->getSession()->executeScript("
                    $('#".$arg1."').val('".$arg2."');
                    $('#".$arg1."').trigger('keyup');
                ");
            }
            if ($findByName) {
                $this->getSession()->executeScript("
                    $('input[name=".$arg1."]').val('".$arg2."');
                    $('input[name=".$arg1."]').trigger('keyup');
                ");
            }
            $this->getSession()->wait(20);
        }
    }

    /**
     * @When I fill and keyup in :arg1 element with :arg2
     */
    public function iFillAndKeyupInElementWith($arg1, $arg2)
    {
        $findBySelector = $this->getSession()->evaluateScript(
            "return ($('".$arg1."').length !=0);"
        );
        if (!$findBySelector) {
            $message = 'Filter Not Found';
            throw new Exception($message);
        } else {
            $this->getSession()->executeScript("
                $('".$arg1."').val('".$arg2."');
                $('".$arg1."').trigger('keyup');
            ");
            $this->getSession()->wait(20);
        }
    }

    /**
     * @When I fill in :arg1 element with :arg2 in :arg3 attribute
     */
    public function iFillInElementWithInAttribute($arg1, $arg2, $arg3)
    {
        $this->getSession()->executeScript("
            $('".$arg1."').attr('".$arg3."','".$arg2."');
        ");
    }

    /**
     * @When I press :arg1 element
     */
    public function iPressElement($arg1)
    {
        $findBySelector = $this->getSession()->evaluateScript(
            "return ($('".$arg1."').length !=0);"
        );
        if (!$findBySelector) {
            $message = 'Element Not Found';
            throw new Exception($message);
        } else {
            $this->getSession()->executeScript("
                $('".$arg1."').click();
            ");
            $this->getSession()->wait(20);
        }
    }

    /**
     * @When I press :arg1 position :arg2 element
     */
    public function iPressPositionElement($arg1, $arg2)
    {
        $findBySelector = $this->getSession()->evaluateScript(
            "return ($('".$arg1."').length !=0);"
        );
        if (!$findBySelector) {
            $message = 'Element Not Found';
            throw new Exception($message);
        } else {
            $this->getSession()->executeScript("
                $('".$arg1."')[".$arg2."].click();
            ");
            $this->getSession()->wait(20);
        }
    }


    /**
     * @When I follow :arg1 element
     */
    public function iFollowElement($arg1)
    {
        $findBySelector = $this->getSession()->evaluateScript(
            "return ($('".$arg1."').length !=0);"
        );
        if (!$findBySelector) {
            $message = 'Element Not Found';
            throw new Exception($message);
        } else {
            $this->getSession()->executeScript("
                window.location.href = $('".$arg1."').attr('href');
            ");
            $this->getSession()->wait(20);
        }
    }

    /**
     * @Given I access row containing :arg1 in :arg2 column
     */
    public function iAccessRowContainingInColumn($arg1,$arg2)
    {
        $this->getSession()->executeScript("
            $('table tbody tr').each(function() {
                var data = $(this).find('td:nth-child(".$arg2.")').text();
                if (data === '".$arg1."') {
                    var url = $(this).find('a').attr('href');
                    window.location.href = url;
                }
            });
        ");
    }

    /**
     * @Given I delete relationship row containing :arg1 in :arg2 column in :arg3 table
     */
    public function iDeleteRelationshipRowContainingInColumnInTable($arg1,$arg2,$arg3)
    {
        $this->getSession()->executeScript("
            $('".$arg3." tbody tr').each(function() {
                var data = $(this).find('td:nth-child(".$arg2.")').text();
                if (data === '".$arg1."') {
                    $(this).find('.delete-relationship').click();
                }
            });
        ");
    }

    /**
      * @Then I should see :arg1 in the :arg2 element :arg3 attribute
      */
    public function iShouldSeeInTheElementAttribute($arg1, $arg2, $arg3)
    {

        $jsEval = $this->getSession()->evaluateScript(
            "($('".$arg2."').attr('".$arg3."') == '".$arg1."')
            "
        );
        if (!$jsEval) {
            $message = $arg1.' not found in the '.$arg3.' attribute of the '.$arg2.' element';
            throw new Exception($message);
        }
    }

    /**
      * @Then I should not see :arg1 in the :arg2 element :arg3 attribute
      */
    public function iShouldNotSeeInTheElementAttribute($arg1, $arg2, $arg3)
    {

        $jsEval = $this->getSession()->evaluateScript(
            "($('".$arg2."').attr('".$arg3."') != '".$arg1."')
            "
        );
        if (!$jsEval) {
            $message = $arg1.' found in the '.$arg3.' attribute of the '.$arg2.' element and it should not appear';
            throw new Exception($message);
        }
    }

    /**
      * @Then I should not see visible an :arg1 element
      */
    public function iShouldNotSeeVisibleAnElement($arg1)
    {
        $jsEval = $this->getSession()->evaluateScript(
            "($('".$arg1."').css('display') == 'none')"
        );
        if (!$jsEval) {
            $message = "The element is visible an it shouldnt";
            throw new Exception($message);
        }
    }

    /**
      * @Then I should see visible an :arg1 element
      */
    public function iShouldSeeVisibleAnElement($arg1)
    {
        $jsEval = $this->getSession()->evaluateScript(
            "($('".$arg1."').css('display') != 'none')"
        );
        if (!$jsEval) {
            $message = "The element is not visible an it should";
            throw new Exception($message);
        }
    }

    /**
      * @Then I wait until I not see visible an :arg1 element
      */
    public function iWaitUntilINotSeeVisibleAnElement($arg1)
    {
        $this->getSession()->wait(
            10000,
            "$('".$arg1."').css('display') == 'none'"
        );
        $jsEval = $this->getSession()->evaluateScript(
            "return ($('".$arg1."').css('display') == 'none');"
        );
        if (!$jsEval) {
            $message = 'The element is still visible';
            throw new Exception($message);
        }
    }

    /**
      * @Then I wait until an :arg1 element is hidden
      */
    public function iWaitUntilAnElementIsHidden($arg1)
    {
        $this->getSession()->wait(
            10000,
            "$('".$arg1."').css('visibility') == 'hidden'"
        );
        $jsEval = $this->getSession()->evaluateScript(
            "return ($('".$arg1."').css('visibility') == 'hidden');"
        );
        if (!$jsEval) {
            $message = 'The element is still visible';
            throw new Exception($message);
        }
    }

    /**
     * @Given I check checkbox :arg1
     */
    public function iCheckCheckbox($arg1)
    {
        $this->getSession()->executeScript("
            $('input:checkbox[value=".$arg1."]').click();
        ");
    }

    /**
     * @When the :arg1 element should be disabled
     */
    public function theElementShouldBeDisabled($arg1)
    {
        $jsEval = $this->getSession()->evaluateScript(
            "$('" . $arg1 . "').prop('disabled') == true;
            "
        );
        if (!$jsEval) {
            $message = 'The input is not disabled and it should be.';
            throw new Exception($message);
        }
    }

    /**
     * @When I select :entry after filling :value in :field
     */
    public function iFillInSelectInputWithAndSelect($entry, $value, $field)
    {
       $page = $this->getSession()->getPage();
       $field = $this->fixStepArgument($field);
       $value = $this->fixStepArgument($value);
       $page->fillField($field, $value);

       $element = $page->findField($field);
       $this->getSession()->getDriver()->keyDown($element->getXpath(), '', null);
       $this->getSession()->wait(500);
       $chosenResults = $page->findAll('css', '.ui-autocomplete a');
       foreach ($chosenResults as $result) {
           if ($result->getText() == $entry) {
               $result->click();
               return;
           }
       }
       throw new \Exception(sprintf('Value "%s" not found', $entry));
    }

}
