<?php

namespace Drupal\Tests\govcore_workflow\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\govcore_workflow\Update\Update330;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\views\Entity\View;

/**
 * Tests configuration updates targeting GovCore Workflow 3.3.0.
 *
 * @group govcore
 * @group govcore_workflow
 *
 * @coversDefaultClass \Drupal\govcore_workflow\Update\Update330
 */
class Update330Test extends KernelTestBase {

  use ContentTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'content_moderation',
    'field',
    'govcore_workflow',
    'node',
    'system',
    'text',
    'user',
    'views',
    'workflows',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig('govcore_workflow');
    $this->installConfig('node');
  }

  /**
   * @covers ::fixModerationHistory
   */
  public function testFixModerationHistory() {
    // Create the moderation_history view in a pre-update state.
    View::create([
      'id' => 'moderation_history',
      'base_table' => 'node_field_revision',
      'display' => [
        'default' => [
          'display_plugin' => 'default',
          'id' => 'default',
          'display_options' => [
            'fields' => [
              'uid' => [
                'id' => 'uid',
                'table' => 'node_field_revision',
                'field' => 'uid',
                'admin_label' => 'Authored by',
                'entity_field' => 'uid',
              ],
              'created' => [
                'id' => 'created',
                'table' => 'node_field_revision',
                'field' => 'created',
                'admin_label' => 'Authored on',
                'entity_field' => 'created',
              ],
              'moderation_state' => [
                'id' => 'moderation_state',
                'table' => 'content_moderation_state_field_revision',
                'field' => 'moderation_state',
                'alter' => [
                  'text' => 'Set to <strong>{{ moderation_state }}</strong> on {{ created }} by {{ uid }}',
                ],
              ],
            ],
            'relationships' => [],
          ],
        ],
      ],
    ])->save();

    // Create a content type that is opted into moderation.
    $this->createContentType([
      'type' => 'page',
      'third_party_settings' => [
        'govcore_workflow' => [
          'workflow' => 'editorial',
        ],
      ],
    ]);

    // Run the update.
    $message = 'Do you want to fix the Moderation History view to prevent incorrect timestamps and authors from being displayed?';
    $io = $this->prophesize('\Symfony\Component\Console\Style\StyleInterface');
    $io->confirm($message)->willReturn(TRUE)->shouldBeCalled();
    Update330::create($this->container)->fixModerationHistory($io->reveal());

    // Assert the view has changed.
    $display = View::load('moderation_history')->getDisplay('default');
    $this->assertSame('array', gettype($display['display_options']['fields']['revision_uid']));
    $this->assertSame('array', gettype($display['display_options']['fields']['revision_timestamp']));
    $this->assertSame('Set to <strong>{{ moderation_state }}</strong> on {{ revision_timestamp }} by {{ revision_uid }}', $display['display_options']['fields']['moderation_state']['alter']['text']);
    $this->assertArrayNotHasKey('uid', $display['display_options']['fields']);
    $this->assertArrayNotHasKey('created', $display['display_options']['fields']);
    $this->assertSame('array', gettype($display['display_options']['relationships']['revision_uid']));
  }

}
