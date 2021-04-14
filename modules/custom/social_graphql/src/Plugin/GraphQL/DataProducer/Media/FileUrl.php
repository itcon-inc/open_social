<?php

namespace Drupal\social_graphql\Plugin\GraphQL\DataProducer\Media;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Convert file URI to URL.
 *
 * @DataProducer(
 *   id = "file_url",
 *   name = @Translation("File url"),
 *   description = @Translation("Convert uri to url."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("File url")
 *   ),
 *   consumes = {
 *     "uri" = @ContextDefinition("string",
 *       label = @Translation("File uri")
 *     ),
 *   }
 * )
 */
class FileUrl extends DataProducerPluginBase {

  /**
   * @param string $path
   * @param mixed $value
   * @param string|null $type
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *
   * @return mixed
   */
  public function resolve($uri, RefinableCacheableDependencyInterface $metadata) {
    $g = 9;
  }

}
