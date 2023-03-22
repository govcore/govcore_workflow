<?php

/**
 * @file
 * Contains post-update functions for GovCore Workflow.
 */

/**
 * Implements hook_removed_post_updates().
 */
function govcore_workflow_removed_post_updates(): array {
  return [
    'govcore_workflow_post_update_import_moderated_content_view' => '4.0.0',
  ];
}
