<?php

namespace Drupal\govcore_workflow\Routing;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\govcore_workflow\Controller\PanelizerIPEController;
use Symfony\Component\Routing\RouteCollection;

/**
 * Reacts to routing events.
 *
 * @internal
 *   This is an internal part of GovCore Workflow's integration with Panelizer
 *   and may be changed or removed at any time. External code should not use
 *   or extend this class in any way!
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $route = $collection->get('panelizer.panels_ipe.revert_to_default');
    if ($route) {
      $route->setDefault('_controller', PanelizerIPEController::class . '::revertToDefault');
    }

    // Ensure that certain routes use the latest revision, rather than the
    // default revision. This can be removed when
    // https://www.drupal.org/project/drupal/issues/2815221 is in core.
    $load_latest_revision = function ($route) use ($collection) {
      $route = $collection->get($route);

      if ($route) {
        $parameters = $route->getOption('parameters');
        $parameters['entity']['load_latest_revision'] = TRUE;
        $route->setOption('parameters', $parameters);
      }
    };
    $load_latest_revision('editor.field_untransformed_text');
    $load_latest_revision('image.upload');
    $load_latest_revision('image.info');
    $load_latest_revision('quickedit.field_form');
  }

  /**
   * Checks if we are currently viewing an entity at its canonical route.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   (optional) The current route match.
   *
   * @return bool
   *   TRUE if we are at the entity's canonical route, FALSE otherwise.
   */
  public static function isViewing(EntityInterface $entity, RouteMatchInterface $route_match = NULL) {
    $route_match = $route_match ?: \Drupal::routeMatch();

    $entity_type = $entity->getEntityTypeId();

    return (
      $route_match->getRouteName() == "entity.$entity_type.canonical" &&
      $route_match->getRawParameter($entity_type) == $entity->id()
    );
  }

}
