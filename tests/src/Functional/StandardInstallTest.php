<?php

namespace Drupal\Tests\govcore_workflow\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\workflows\Entity\Workflow;

/**
 * Tests that our configuration is correctly installed in Standard.
 *
 * @group govcore_workflow
 */
class StandardInstallTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected $profile = 'standard';

  /**
   * Tests that the editorial workflow is installed from the profile override.
   */
  public function testWorkflowNotExists() {
    $this->assertTrue($this->config('workflows.workflow.editorial')->isNew());

    $this->container->get('module_installer')->install(['govcore_workflow']);

    /** @var \Drupal\workflows\WorkflowInterface $workflow */
    $workflow = Workflow::load('editorial');
    $this->assertInstanceOf(Workflow::class, $workflow);
    $this->assertFalse($workflow->getTypePlugin()->hasState('review'));
  }

}
