<?php

namespace Drupal\Tests\govcore_scheduler\Kernel\Update;

use Drupal\KernelTests\KernelTestBase;

/**
 * @group govcore_scheduler
 */
class Update8003Test extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'content_moderation',
    'govcore_scheduler',
    'system',
    'user',
  ];

  /**
   * Tests that the config object is cresated.
   */
  public function testUpdate() {
    // Assert the config object does not already exist.
    $this->assertTrue($this->config('govcore_scheduler.settings')->isNew());

    // Run the update.
    $this->container->get('module_handler')
      ->loadInclude('govcore_scheduler', 'install');
    govcore_scheduler_update_8003();

    // Assert the config object was created.
    $time_step = $this->config('govcore_scheduler.settings')
      ->get('time_step');
    $this->assertSame(60, $time_step);
  }

}
