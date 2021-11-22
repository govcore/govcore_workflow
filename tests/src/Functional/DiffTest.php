<?php

namespace Drupal\Tests\govcore_workflow\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests GovCore Workflow's integration with Diff.
 *
 * @group govcore_workflow
 */
class DiffTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'diff',
    'govcore_workflow',
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
    $this->drupalPlaceBlock('local_tasks_block');
  }

  /**
   * Tests basic Diff functionality.
   */
  public function testDiffIntegration() {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    $account = $this->drupalCreateUser([
      'access content overview',
      'edit any moderated content',
      'use editorial transition create_new_draft',
      'view all revisions',
      'view any unpublished content',
    ]);
    $this->drupalLogin($account);

    $this->drupalCreateNode([
      'type' => 'moderated',
      'title' => 'Pastafazoul',
      'body' => 'First revision',
      'moderation_state' => 'draft',
    ]);
    $this->drupalGet('/admin/content');
    $page->clickLink('Pastafazoul');
    $assert_session->elementExists('named', ['link', 'edit-form'])->click();
    $page->fillField('body[0][value]', 'Second revision');
    $page->pressButton('Save');
    $assert_session->elementExists('named', ['link', 'edit-form'])->click();
    $page->fillField('body[0][value]', 'Third revision');
    $page->pressButton('Save');
    $page->clickLink('Revisions');

    $rows = $page->findAll('css', '.diff-revisions tbody tr');
    $rows = array_reverse($rows);
    $a = $rows[0]->findField('radios_left')->getValue();
    $b = $rows[1]->findField('radios_right')->getValue();

    $page->selectFieldOption('radios_left', $a);
    $page->selectFieldOption('radios_right', $b);
    $page->pressButton('Compare');
    $assert_session->pageTextContains('Changes to Pastafazoul');
  }

}
