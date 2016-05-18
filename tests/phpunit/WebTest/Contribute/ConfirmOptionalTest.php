<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2016                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */

require_once 'CiviTest/CiviSeleniumTestCase.php';

/**
 * Class WebTest_Contribute_ConfirmOptionalTest
 */
class WebTest_Contribute_ConfirmOptionalTest extends CiviSeleniumTestCase {
  protected $pageId = 0;

  protected function setUp() {
    parent::setUp();
  }

  public function testWithConfirm() {
    $this->_addContributionPage(TRUE);
    $this->_fillOutContributionPage();

    // confirm contribution
    $this->assertFalse($this->isTextPresent("Your transaction has been processed successfully"), "Loaded thank you page");
    $this->waitForElementPresent("_qf_Confirm_next-bottom");
    $this->assertTrue($this->isTextPresent("Please verify the information below carefully"), "Should load confirmation page");
    $this->click("_qf_Confirm_next-bottom");
    $this->waitForPageToLoad($this->getTimeoutMsec());

    // thank you page
    $this->assertTrue($this->isTextPresent("Your transaction has been processed successfully"), "Should load thank you page");
  }

  public function testWithoutConfirm() {
    $this->_addContributionPage(FALSE);
    $this->_fillOutContributionPage();

    // thank you page
    $this->assertTrue($this->isTextPresent("Your transaction has been processed successfully"), "Didn't load thank you page after main page");
    $this->assertFalse($this->isTextPresent("Your contribution will not be completed until"), "Loaded confirmation page");
  }

  /**
   * Test CRM-18387
   *
   * @param $dateFormat
   * @param $timeFormat
   */
  public function testCustomDateFieldFormat($dateFormat = "DD, d MM yy", $timeFormat = NULL) {
    $this->webtestLogin();

    $hash = substr(sha1(rand()), 0, 7);

    // add new custom field set
    $this->openCiviPage("admin/custom/group", "action=add&reset=1", "id=title");
    // $this->waitForPageToLoad("30000");
    $this->type("id=title", "Test Custom Field Set ($hash)");
    $this->select("id=extends_0", "label=Contributions");
    $this->clickLink("id=_qf_Group_next-bottom", "id=label");
    // $this->waitForPageToLoad("30000");

    // add new custom field
    // $this->waitForElementPresent("id=label");
    $this->type("id=label", "Test Custom Field ($hash)");
    $this->select("id=data_type_0", "label=Date");
    $this->select("id=date_format", "label=MM d, yyyy (December 31, 2009)");
    if ($timeFormat) {
      $this->select("id=time_format", "value=$timeFormat");
    }
    $this->click("id=_qf_Field_done-top");

    // add new profile
    $this->openCiviPage("admin/uf/group/add", "action=add&reset=1", "id=title");
    $this->type("id=title", "Test Profile ($hash)");
    $this->click("id=_qf_Group_next-bottom");
    $this->clickLink("id=_qf_Group_next-bottom", "id=field_name_0");
    // $this->waitForPageToLoad("30000");

    // add a custom field to profile
    // $this->waitForElementPresent("id=field_name_0");
    $this->select("id=field_name_0", "label=Contributions");
    $this->select("id=field_name_1", "label=Test Custom Field ($hash) :: Test Custom Field Set ($hash)");
    $this->click("id=_qf_Field_next-top");
    // $this->waitForPageToLoad("30000");

    // add a new price set
    $this->openCiviPage("admin/price", "reset=1&action=add", "id=title");
    $this->pause( 4000 );
    $this->type("id=title", "Test Price Set ($hash)");
    $this->click("id=extends_2");
    $this->select("id=financial_type_id", "label=Campaign Contribution");
    $this->pause( 3000 );
    $this->clickLink("id=_qf_Set_next-bottom", "id=label");
    // ERROR: Instead of showing "Add New Price Field" dialog, redirects to civirm/admin/price page
    // Selenium keeps waiting for "id=label"
    $this->type("id=label", "Test Price Field Label ($hash)");
    $this->type("id=price", "100");
    $this->click("id=_qf_Field_next-top");

    // CODE BELOW THIS WORKS!!

    // add a new contribution page
    $this->openCiviPage("admin/contribute/add", "reset=1&action=add", "id=title");
    // $this->waitForPageToLoad("30000");
    $this->type("id=title", "Test Contribution Page ($hash)");
    $this->select("financial_type_id", "label=Campaign Contribution");
    $this->clickLink("id=_qf_Settings_next-bottom", "id=price_set_id");
    // $this->click("id=_qf_Settings_next-bottom");
    // $this->waitForPageToLoad("30000");
    // $this->waitForElementPresent("id=price_set_id");
    
    // get contribution page id
    $pageId = $this->urlArg('id');
    $this->select("id=price_set_id", "label=New Test Price Set");
    // $this->select("id=price_set_id", "label=Test Price Set ($hash)");
    $this->clickLink("id=_qf_Amount_next-bottom", "id=ui-id-11");
    // $this->waitForPageToLoad("30000");

    $this->click("id=ui-id-11");
    $this->waitForElementPresent("id=_qf_Custom_upload_done-bottom");
    
    $this->select('css=tr.crm-contribution-contributionpage-custom-form-block-custom_pre_id span.crm-profile-selector-select select', "label=Test Profile (2ad4995)");
    // $this->select('css=tr.crm-contribution-contributionpage-custom-form-block-custom_pre_id span.crm-profile-selector-select select', "label=New Test Profile ($hash)");
    $this->click("id=_qf_Custom_upload_done-bottom");
    $this->waitForPageToLoad("30000");

    $this->openCiviPage("contribute/transact", "reset=1&id=$pageId");
    // $this->waitForPageToLoad("30000");

    $this->type("css=input[id^=price_]", "100");
    $this->type("id=email-5", "demo@demo.org");
    $this->waitForElementPresent("css=div#ui-datepicker-div.ui-datepicker div.ui-datepicker-header div.ui-datepicker-title span.ui-datepicker-month");
    $this->select("css=div#ui-datepicker-div div.ui-datepicker-header div.ui-datepicker-title select.ui-datepicker-year", "value=2016");
    $this->click("link=19");
    $this->pause( 3000 );
    $this->click("id=_qf_Main_upload-bottom");
    $this->waitForPageToLoad("30000");

    $actualPHPFormats = CRM_Core_SelectValues::datePluginToPHPFormats();
    $dateFormat = CRM_Utils_Array::value($dateFormat, $actualPHPFormats);

    if ( $timeFormat ) {
      $timeFormat = ($timeFormat == 1) ? "h:i A" : "H:i";
    }
    $dateTimeFormat = $dateFormat." ".$timeFormat;

    $dateTimeText = date($dateTimeFormat, strtotime("2016-05-19 10:30 AM"));
    
    // test format on confirmation page
    $this->assertText('css=span.crm-frozen-field', $dateTimeText);

    $this->click("id=_qf_Confirm_next-bottom");
    $this->waitForPageToLoad("30000");

    // test format on thank you page
    // I realized I hadn't fixed this one. Need to fix date format on Thank You page
    $this->assertText('css=span.crm-frozen-field', $dateTimeText);
  }

  /**
   * @param $isConfirmEnabled
   */
  protected function _addContributionPage($isConfirmEnabled) {
    // log in
    $this->webtestLogin();

    // create new contribution page
    $hash = substr(sha1(rand()), 0, 7);
    $this->pageId = $this->webtestAddContributionPage(
      $hash,
      $rand = NULL,
      $pageTitle = "Test Confirm ($hash)",
      $processor = array("Dummy ($hash)" => 'Dummy'),
      $amountSection = TRUE,
      $payLater = FALSE,
      $onBehalf = FALSE,
      $pledges = FALSE,
      $recurring = FALSE,
      $membershipTypes = FALSE,
      $memPriceSetId = NULL,
      $friend = FALSE,
      $profilePreId = NULL,
      $profilePostId = NULL,
      $premiums = FALSE,
      $widget = FALSE,
      $pcp = FALSE,
      $isAddPaymentProcessor = TRUE,
      $isPcpApprovalNeeded = FALSE,
      $isSeparatePayment = FALSE,
      $honoreeSection = FALSE,
      $allowOtherAmount = TRUE,
      $isConfirmEnabled = $isConfirmEnabled
    );
  }

  protected function _fillOutContributionPage() {
    // load contribution page
    $this->openCiviPage("contribute/transact", "reset=1&id={$this->pageId}&action=preview", "_qf_Main_upload-bottom");

    // fill out info
    $this->type("xpath=//div[@class='crm-section other_amount-section']//div[2]/input", "30");
    $this->webtestAddCreditCardDetails();
    list($firstName, $middleName, $lastName) = $this->webtestAddBillingDetails();
    $this->type('email-5', "$lastName@example.com");

    // submit contribution
    $this->click("_qf_Main_upload-bottom");
    $this->waitForPageToLoad($this->getTimeoutMsec());
  }

}
