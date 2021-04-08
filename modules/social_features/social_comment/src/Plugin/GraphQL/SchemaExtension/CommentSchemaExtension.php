<?php

namespace Drupal\social_comment\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;

/**
 * @SchemaExtension(
 *   id = "open_social_comment",
 *   name = "Example extension",
 *   description = "A simple extension that adds node related fields.",
 *   schema = "open_social"
 * )
 */
class CommentSchemaExtension extends SdlSchemaExtensionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry) {
    $builder = new ResolverBuilder();

    $this->addQueryFields($registry, $builder);
    $this->addTopicFields($registry, $builder);
  }

  /**
   * @param \Drupal\graphql\GraphQL\ResolverRegistryInterface $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  protected function addTopicFields(ResolverRegistryInterface $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('Comment', 'message',
      $builder->produce('property_path')
        ->map('type', $builder->fromValue('entity:comment:comment'))
        ->map('value', $builder->fromParent())
        ->map('path', $builder->fromValue('field_comment_body.value'))
    );

    $registry->addFieldResolver('Comment', 'id',
      $builder->produce('entity_uuid')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('Comment', 'cid',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );
  }

  /**
   * @param \Drupal\graphql\GraphQL\ResolverRegistryInterface $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  protected function addQueryFields(ResolverRegistryInterface $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('Query', 'comments',
      $builder->produce('social_comments')
        ->map('entity', $builder->fromArgument('entity'))
        ->map('after', $builder->fromArgument('after'))
        ->map('before', $builder->fromArgument('before'))
        ->map('first', $builder->fromArgument('first'))
        ->map('last', $builder->fromArgument('last'))
        ->map('reverse', $builder->fromArgument('reverse'))
        ->map('sortKey', $builder->fromArgument('sortKey'))
    );

    $registry->addFieldResolver('Query', 'comment',
      $builder->produce('entity_load_by_uuid')
        ->map('type', $builder->fromValue('comment'))
        ->map('bundles', $builder->fromValue(['comment']))
        ->map('uuid', $builder->fromArgument('id'))
    );
  }

}
