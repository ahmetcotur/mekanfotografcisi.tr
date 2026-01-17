<?php
/**
 * Core Template Loader Class
 * Handles finding and loading templates based on the WordPress hierarchy.
 */

namespace Core;

class TemplateLoader
{
    private $templateDir;
    private $post;

    public function __construct($templateDir, $post = null)
    {
        $this->templateDir = rtrim($templateDir, '/');
        $this->post = $post;
    }

    /**
     * Find the best template for the current post
     */
    public function getTemplate()
    {
        if (!$this->post) {
            return $this->findTemplate(['404.php', 'index.php']);
        }

        $type = $this->post->post_type;
        $slug = $this->post->slug;

        $hierarchy = [];

        if ($type === 'page') {
            $hierarchy[] = "page-{$slug}.php";
            $hierarchy[] = "page-{$this->post->id}.php";
            $hierarchy[] = "page.php";
        } elseif ($type === 'service') {
            $hierarchy[] = "single-service-{$slug}.php";
            $hierarchy[] = "single-service.php";
            $hierarchy[] = "single.php";
        } elseif ($type === 'portfolio') {
            $hierarchy[] = "single-portfolio-{$slug}.php";
            $hierarchy[] = "single-portfolio.php";
            $hierarchy[] = "single.php";
        } elseif ($type === 'location' || $type === 'seo_page') {
            $hierarchy[] = "single-location-{$slug}.php";
            $hierarchy[] = "single-location.php";
            $hierarchy[] = "single-seo_page.php"; // Added specific template
            $hierarchy[] = "single.php";
        } elseif ($type === 'blog') {
            $hierarchy[] = "single-blog-{$slug}.php";
            $hierarchy[] = "single-blog.php";
            $hierarchy[] = "single.php";
        }

        $hierarchy[] = "singular.php";
        $hierarchy[] = "index.php";

        return $this->findTemplate($hierarchy);
    }

    /**
     * Search for templates in order
     */
    private function findTemplate($files)
    {
        foreach ($files as $file) {
            $path = $this->templateDir . '/' . $file;
            if (file_exists($path)) {
                return $path;
            }
        }
        return null;
    }

    /**
     * Render the template with the post data
     */
    public function render($templatePath)
    {
        if (!$templatePath || !file_exists($templatePath)) {
            http_response_code(404);
            echo "Template not found: " . basename($templatePath);
            return;
        }

        // Make $post available to the template
        $post = $this->post;

        include $templatePath;
    }
}
