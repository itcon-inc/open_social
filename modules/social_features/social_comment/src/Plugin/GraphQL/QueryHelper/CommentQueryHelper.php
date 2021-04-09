<?php

namespace Drupal\social_comment\Plugin\GraphQL\QueryHelper;

use Drupal\comment\Entity\Comment;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\node\Entity\Node;
use Drupal\social_graphql\GraphQL\ConnectionQueryHelperInterface;
use Drupal\social_graphql\Wrappers\Cursor;
use Drupal\social_graphql\Wrappers\Edge;
use GraphQL\Deferred;
use GraphQL\Executor\Promise\Adapter\SyncPromise;

/**
 * Loads comments.
 */
class CommentQueryHelper implements ConnectionQueryHelperInterface {

  /**
   * The conversations for which participants are being fetched.
   */
  protected $entity;

  /**
   * The Drupal entity type manager.
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The key that is used for sorting.
   */
  protected string $sortKey;

  /**
   * ConversationParticipantsQueryHelper constructor.
   *
   * @param mixed $entity
   *   The conversations for which participants are being fetched.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The Drupal entity type manager.
   * @param string $sort_key
   *   The key that is used for sorting.
   */
  public function __construct($entity, EntityTypeManagerInterface $entity_type_manager, string $sort_key) {
    $this->entity = $entity;
    $this->entityTypeManager = $entity_type_manager;
    $this->sortKey = $sort_key;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery() : QueryInterface {
    $query = $this->entityTypeManager->getStorage('comment')
      ->getQuery()
      ->currentRevision()
      ->accessCheck()
      // Exclude the anonymous user from listings because it doesn't make sense
      // in overview pages.
      ->condition('uid', 0, '!=');
    if ($this->entity instanceof Node) {
      $query->condition('entity_id', $this->entity->id());
    }

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function getCursorObject(string $cursor) : ?Cursor {
    $cursor_object = Cursor::fromCursorString($cursor);

    return !is_null($cursor_object) && $cursor_object->isValidFor($this->sortKey, 'comment')
      ? $cursor_object
      : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getIdField() : string {
    return 'cid';
  }

  /**
   * {@inheritdoc}
   */
  public function getSortField() : string {
    switch ($this->sortKey) {
      case 'CREATED_AT':
        return 'created';

      default:
        throw new \InvalidArgumentException("Unsupported sortKey for sorting '{$this->sortKey}'");
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getAggregateSortFunction() : ?string {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getLoaderPromise(array $result) : SyncPromise {
    // In case of no results we create a callback the returns an empty array.
    if (empty($result)) {
      $callback = static fn () => [];
    }
    // Otherwise we create a callback that uses the GraphQL entity buffer to
    // ensure the entities for this query are only loaded once. Even if the
    // results are used multiple times.
    else {
      $buffer = \Drupal::service('graphql.buffer.entity');
      $callback = $buffer->add('comment', array_values($result));
    }

    return new Deferred(
      function () use ($callback) {
        return array_map(
          fn (Comment $entity) => new Edge(
            $entity,
            new Cursor('comment', $entity->id(), $this->sortKey, $this->getSortValue($entity))
          ),
          $callback()
        );
      }
    );
  }

  /**
   * Get the value for an entity based on the sort key for this connection.
   *
   * @param \Drupal\comment\Entity\Comment $comment
   *   The participant entity for the user in this conversation.
   *
   * @return mixed
   *   The sort value.
   */
  protected function getSortValue(Comment $comment) {
    switch ($this->sortKey) {
      case 'CREATED_AT':
        return $comment->getCreatedTime();

      default:
        throw new \InvalidArgumentException("Unsupported sortKey for pagination '{$this->sortKey}'");
    }
  }

}
