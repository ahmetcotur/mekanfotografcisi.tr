<?php
/**
 * Core Query Class
 * Handles fetching posts based on various criteria.
 */

namespace Core;

class Query
{
    private $db;
    public $posts = [];
    public $found_posts = 0;

    public function __construct($args = [], $db)
    {
        $this->db = $db;
        $this->query($args);
    }

    public function query($args)
    {
        $defaults = [
            'post_type' => 'page',
            'post_status' => 'publish',
            'posts_per_page' => 10,
            'offset' => 0,
            'orderby' => 'created_at',
            'order' => 'DESC',
            'meta_query' => []
        ];

        $params = array_merge($defaults, $args);

        // Construct basic query params for DatabaseClient
        $selectParams = [
            'post_type' => $params['post_type'],
            'post_status' => $params['post_status'],
            'limit' => $params['posts_per_page'],
            'offset' => $params['offset'],
            'order' => $params['orderby'] . ' ' . $params['order']
        ];

        // Currently simplified: DatabaseClient select() doesn't support complex JOINs or meta queries natively
        // In a real WordPress-like system, this would build a complex SQL string with JOINs for meta_query.
        // For now, we fetch the posts and we'll handle meta filtering in PHP or extend DatabaseClient later.

        $results = $this->db->select('posts', $selectParams);

        foreach ($results as $data) {
            $this->posts[] = new Post($data, $this->db);
        }

        $this->found_posts = count($this->posts);

        return $this->posts;
    }

    public function havePosts()
    {
        return !empty($this->posts);
    }
}
