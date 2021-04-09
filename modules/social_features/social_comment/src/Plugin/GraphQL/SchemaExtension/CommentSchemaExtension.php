<?php

namespace Drupal\social_comment\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;

/**
 * @SchemaExtension(
 *   id = "open_social_comment",
 *   name = "Open Social - Comment Schema Extension",
 *   description = "GraphQL schema extension for Open Social comment data.",
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
    $this->addCommentFields($registry, $builder);
  }

  /**
   * @param \Drupal\graphql\GraphQL\ResolverRegistryInterface $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  protected function addCommentFields(ResolverRegistryInterface $registry, ResolverBuilder $builder) {
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

    $registry->addFieldResolver('Comment', 'attachments',
      $builder->produce('social_files')
        ->map('entity', $builder->fromParent())
        ->map('after', $builder->fromArgument('after'))
        ->map('before', $builder->fromArgument('before'))
        ->map('first', $builder->fromArgument('first'))
        ->map('last', $builder->fromArgument('last'))
        ->map('reverse', $builder->fromArgument('reverse'))
        ->map('sortKey', $builder->fromArgument('sortKey'))
    );

    $registry->addFieldResolver('Attachment', 'id',
      $builder->produce('entity_uuid')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('Attachment', 'fid',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('Attachment', 'url',
      $builder->produce('property_path')
        ->map('type', $builder->fromValue('entity:file'))
        ->map('value', $builder->fromParent())
        ->map('path', $builder->fromValue('uri.value'))
    );

    $registry->addFieldResolver('Attachment', 'filename',
      $builder->produce('property_path')
        ->map('type', $builder->fromValue('entity:file'))
        ->map('value', $builder->fromParent())
        ->map('path', $builder->fromValue('filename.value'))
    );

    $registry->addFieldResolver('Attachment', 'filemime',
      $builder->produce('property_path')
        ->map('type', $builder->fromValue('entity:file'))
        ->map('value', $builder->fromParent())
        ->map('path', $builder->fromValue('filemime.value'))
    );

    $registry->addFieldResolver('Attachment', 'filesize',
      $builder->produce('property_path')
        ->map('type', $builder->fromValue('entity:file'))
        ->map('value', $builder->fromParent())
        ->map('path', $builder->fromValue('filesize.value'))
    );

    $registry->addFieldResolver('Attachment', 'created',
      $builder->produce('property_path')
        ->map('type', $builder->fromValue('entity:file'))
        ->map('value', $builder->fromParent())
        ->map('path', $builder->fromValue('created.value'))
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
