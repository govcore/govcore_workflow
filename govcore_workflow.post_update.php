<?php

/**
 * @file
 * Contains post-update functions for GovCore Workflow.
 */

use Drupal\govcore_core\ConfigHelper as Config;

/**
 * Imports the moderated_content view.
 */
function govcore_workflow_post_update_import_moderated_content_view() {
  if (Drupal::moduleHandler()->moduleExists('views')) {
    Config::forModule('content_moderation')
      ->optional()
      ->getEntity('view', 'moderated_content')
      ->save();
  }
}
