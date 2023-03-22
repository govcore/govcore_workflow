<?php

namespace Drupal\Tests\govcore_scheduler\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the GovCore Scheduler settings form.
 *
 * @group govcore_scheduler
 * @group govcore_workflow
 */
class SettingsFormTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['govcore_scheduler'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests that administrators can access the settings form.
   */
  public function testAccess(): void {
    $account = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($account);
    $this->drupalGet('/admin/config/system/govcore/scheduler');
    $this->assertSession()->statusCodeEquals(200);
  }

}
