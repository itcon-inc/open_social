<?php

namespace Drupal\Tests\social_comment\Kernel\GraphQL;

use Drupal\comment\Entity\Comment;
use Drupal\comment\Tests\CommentTestTrait;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\social_graphql\Kernel\SocialGraphQLTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\user\Entity\User;

/**
 * Tests the comments endpoint added to the Open Social schema by this module.
 *
 * This uses the GraphQLTestBase which extends KernelTestBase since this class
 * is interested in testing the implementation of the GraphQL schema that's a
 * part of this module. We're not interested in the HTTP functionality since
 * that is covered by the graphql module itself. Thus BrowserTestBase is not
 * needed.
 *
 * @group social_graphql
 */
class GraphQLCommentsEndpointTest extends SocialGraphQLTestBase {

  use CommentTestTrait;
  use NodeCreationTrait;
  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'social_comment',
    'comment',
    'field',
    'filter',
    'node',
    'text',
    'user',
  ];

  /**
   * The list of comments.
   *
   * @var \Drupal\comment\CommentInterface[]
   */
  private $comments = [];

  /**
   * {@inheritdoc}
   */
  protected function setUp() : void {
    parent::setUp();
//    $this->installEntitySchema('user');
//    $this->installEntitySchema('node');
//    $this->installEntitySchema('comment');
//    $this->installSchema('comment', ['comment_entity_statistics']);
//    $this->installConfig(['filter']);
////    $this->installConfig(['filter', 'node', 'social_page']);


    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('comment');
    $this->installSchema('comment', ['comment_entity_statistics']);
    $this->installConfig(['filter']);

    \Drupal::currentUser()->setAccount(User::load(1));

    NodeType::create(['type' => 'page'])->save();

    FieldStorageConfig::create([
      'type' => 'text_long',
      'entity_type' => 'comment',
      'field_name' => 'comment_body',
    ])->save();

    $this->addDefaultCommentField('node', 'page', 'comment');
    $account = $this->createUser();

    $node_commented_by_account = $this->createNode([
      'type' => 'page',
      'title' => "commented by {$account->id()}",
    ]);

    for ($i = 0; $i < 10; ++$i) {
      $this->comments[] = $this->createComment($account, $node_commented_by_account);
    }
  }

  /**
   * Test the filter for the comments query.
   */
  public function testCommentsQueryFilter(): void {
    $this->assertEndpointSupportsPagination(
      'comments',
      array_map(static fn ($comment) => $comment->uuid(), $this->comments)
    );
  }

  /**
   * Ensure that the fields for the comment endpoint properly resolve.
   *
   * This test does not test the validity of the resolved data but merely that
   * the API contract is adhered to.
   */
  public function testCommentFieldsPresence() : void {
    $account = $this->createUser();
    $node_commented_by_account = $this->createNode([
      'title' => "commented by {$account->id()}",
    ]);

    $this->setCurrentUser(User::load(1));
    $test_comment = $this->createComment($account, $node_commented_by_account);

    $query = '
      query ($id: ID!) {
        comment(id: $id) {
          id
          body
        }
      }
    ';
    $expected_data = [
      'comment' => [
        'id' => $test_comment->uuid(),
        'body' => 'test'
      ],
    ];

    $this->assertResults(
      $query,
      ['id' => $test_comment->uuid()],
      $expected_data,
      $this->defaultCacheMetaData()
        ->addCacheableDependency($test_comment)
        // @todo It's unclear why this cache context is added.
        ->addCacheContexts(['languages:language_interface'])
    );
  }


  private function createComment($account, $node_commented_by_account) {
    $comment = Comment::create([
      'uid' => $account->id(),
      'entity_id' => $node_commented_by_account->id(),
      'entity_type' => 'node',
      'field_name' => 'comment',
      'comment_body' => 'test',
    ]);

    $comment->save();
    return $comment;
  }

}
