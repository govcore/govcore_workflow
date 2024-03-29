<?php

namespace Drupal\Tests\govcore_workflow\FunctionalJavascript;

use Behat\Mink\Element\TraversableElement;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests GovCore Workflow's integration with Quick Edit.
 *
 * @group govcore_workflow
 */
class QuickEditTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'govcore_page',
    'govcore_workflow',
    'quickedit',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('system_main_block');
  }

  /**
   * Tests that Quick Edit is enabled when viewing unpublished content.
   */
  public function testQuickEditEnabledForUnpublishedContent() {
    $page = $this->getSession()->getPage();

    $account = $this->drupalCreateUser([
      'access content overview',
      'access in-place editing',
      'access contextual links',
      'edit any page content',
      'use editorial transition create_new_draft',
      'view any unpublished content',
    ]);
    $this->drupalLogin($account);

    $title = $this->drupalCreateNode(['type' => 'page'])->getTitle();

    $this->drupalGet('/admin/content');
    $page->clickLink($title);
    $this->assertQuickEditEnabled();
  }

  /**
   * Tests that Quick Edit is disabled when viewing published content.
   */
  public function testQuickEditDisabledForPublishedContent() {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    $account = $this->drupalCreateUser([
      'use editorial transition publish',
      'view own unpublished content',
      'access in-place editing',
      'access contextual links',
      'view any unpublished content',
      'edit any page content',
    ]);
    $this->drupalLogin($account);

    $node = $this->drupalCreateNode(['type' => 'page']);
    $this->drupalGet($node->toUrl());
    $assert_session->elementExists('css', 'a[rel="edit-form"]')->click();
    $page->selectFieldOption('moderation_state[0][state]', 'Published');
    $page->pressButton('Save');
    $assert_session->addressMatches('|^/node/[0-9]+$|');
    $this->assertJsCondition('Drupal.quickedit.collections.entities.length === 0');
  }

  /**
   * Test that Quick Edit is enabled when viewing a pending revision.
   */
  public function testQuickEditEnabledForPendingRevisions() {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    $account = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($account);

    $node = $this->drupalCreateNode([
      'type' => 'page',
      'moderation_state' => 'published',
    ]);
    $this->drupalGet($node->toUrl());
    $assert_session->elementExists('css', 'a[rel="edit-form"]')->click();
    $page->selectFieldOption('moderation_state[0][state]', 'Draft');
    $page->pressButton('Save');
    $assert_session->addressMatches('|^/node/[0-9]+/latest$|');
    $this->assertQuickEditEnabled();

    $link_exists = $assert_session->waitForElement('css', 'div[data-block-plugin-id="system_main_block"] ul.contextual-links li.quickedit');
    $this->assertNotEmpty($link_exists);
  }

  /**
   * Asserts that Quick Edit is enabled on the current page.
   */
  private function assertQuickEditEnabled() {
    $this->assertJsCondition('Drupal.quickedit.collections.entities.length > 0');
  }

}
