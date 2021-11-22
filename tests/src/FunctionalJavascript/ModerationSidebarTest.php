<?php

namespace Drupal\Tests\govcore_workflow\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests GovCore Workflow's integration with Moderation Sidebar.
 *
 * @group govcore_workflow
 */
class ModerationSidebarTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'govcore_workflow',
    'moderation_sidebar',
    'toolbar',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->drupalCreateContentType([
      'type' => 'moderated',
      'third_party_settings' => [
        'govcore_workflow' => [
          'workflow' => 'editorial',
        ],
      ],
    ]);
  }

  /**
   * Tests basic Moderation Sidebar functionality.
   */
  public function testModerationSidebar() {
    $assert_session = $this->assertSession();

    $account = $this->drupalCreateUser([
      'access toolbar',
      'edit own moderated content',
      'use editorial transition publish',
      'use editorial transition review',
      'use moderation sidebar',
      'view any unpublished content',
    ]);
    $this->drupalLogin($account);

    $node = $this->drupalCreateNode(['type' => 'moderated']);
    $this->assertSame('draft', $node->moderation_state->value);
    $this->drupalGet($node->toUrl());

    $toolbar = $assert_session->elementExists('css', '#toolbar-bar');
    $toolbar->clickLink('Tasks');

    $sidebar = $assert_session->waitForElementVisible('css', '.moderation-sidebar-container');
    $this->assertNotEmpty($sidebar);

    $sidebar->pressButton('Publish');
    $assert_session->pageTextContains('The moderation state has been updated.');
    $this->assertSame('Published', $assert_session->elementExists('named', ['link', 'Tasks'], $toolbar)->getAttribute('data-label'));
  }

}
