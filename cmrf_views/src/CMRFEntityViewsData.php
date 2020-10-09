<?php namespace Drupal\cmrf_views;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\views\EntityViewsDataInterface;

class CMRFEntityViewsData implements EntityViewsDataInterface {

  public function getViewsData() {
    $views = \Drupal::service('cmrf_views.views');
    $cache = $cache = \Drupal::cache('discovery');
    $cacheEntry = $cache->get('cmrf_views.views.data');
    if($cacheEntry) {
      return $cacheEntry->data;
    } else {
     $viewsData = $views->getViewsData();
     $cache->set('cmrf_views.views.data',$viewsData);
     return $viewsData;
    }
  }

  public function getViewsTableForEntityType(EntityTypeInterface $entity_type) {
    // TODO: Implement getViewsTableForEntityType() method.
  }

}
