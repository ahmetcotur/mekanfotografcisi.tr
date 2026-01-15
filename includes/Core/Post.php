<?php
/**
 * Core Post Class
 * Represents a piece of content in the system.
 */

namespace Core;

class Post
{
    public $id;
    public $title;
    public $slug;
    public $content;
    public $excerpt;
    public $post_type;
    public $post_status;
    public $parent_id;
    public $menu_order;
    public $gallery_folder_id;
    public $created_at;
    public $updated_at;

    private $meta = [];
    private $db;

    public function __construct($data = [], $db = null)
    {
        $this->db = $db;
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Get a post by its slug
     */
    public static function findBySlug($slug, $db)
    {
        // Try exact match first
        $results = $db->select('posts', [
            'slug' => $slug,
            'post_status' => 'publish',
            'limit' => 1
        ]);

        // If not found, try with leading slash (legacy slugs)
        if (empty($results)) {
            $results = $db->select('posts', [
                'slug' => '/' . $slug,
                'post_status' => 'publish',
                'limit' => 1
            ]);
        }

        // 3. Smart handle for prefixes
        if (empty($results)) {
            // If searching for hizmetlerimiz/slug or services/slug, try finding just slug
            $cleanSlug = preg_replace('/^(hizmetlerimiz\/|services\/)/', '', $slug);
            if ($cleanSlug !== $slug) {
                $results = $db->select('posts', [
                    'slug' => $cleanSlug,
                    'post_status' => 'publish',
                    'limit' => 1
                ]);
            }
        }

        // Also try common path patterns if last part matches
        if (empty($results)) {
            $results = $db->query("SELECT * FROM posts WHERE slug LIKE ? AND post_status = 'publish' LIMIT 1", ["%/$slug"]);
        }

        if (empty($results)) {
            return null;
        }

        return new self($results[0], $db);
    }

    /**
     * Get a post by its ID
     */
    public static function findById($id, $db)
    {
        $results = $db->select('posts', [
            'id' => $id,
            'limit' => 1
        ]);

        if (empty($results)) {
            return null;
        }

        return new self($results[0], $db);
    }

    /**
     * Get metadata for the post
     */
    public function getMeta($key = null, $single = true)
    {
        if (empty($this->meta) && $this->id) {
            $this->loadMeta();
        }

        if ($key === null) {
            return $this->meta;
        }

        if (!isset($this->meta[$key])) {
            return $single ? null : [];
        }

        return $single ? $this->meta[$key][0] : $this->meta[$key];
    }

    /**
     * Load all metadata for this post
     */
    private function loadMeta()
    {
        if (!$this->id || !$this->db)
            return;

        $results = $this->db->select('post_meta', [
            'post_id' => $this->id
        ]);

        foreach ($results as $row) {
            $key = $row['meta_key'];
            $value = json_decode($row['meta_value'], true);
            if (!isset($this->meta[$key])) {
                $this->meta[$key] = [];
            }
            $this->meta[$key][] = $value;
        }
    }

    /**
     * Get children of this post
     */
    public function getChildren()
    {
        if (!$this->id || !$this->db)
            return [];

        $results = $this->db->select('posts', [
            'parent_id' => $this->id,
            'post_status' => 'publish',
            'order' => 'menu_order ASC'
        ]);

        $children = [];
        foreach ($results as $data) {
            $children[] = new self($data, $this->db);
        }
        return $children;
    }
}
