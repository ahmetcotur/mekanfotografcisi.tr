<?php
/**
 * Simple Markdown to HTML Converter
 */

function markdownToHtml($markdown) {
    if (empty($markdown)) {
        return '';
    }
    
    $html = $markdown;
    
    // Headings
    $html = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $html);
    $html = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $html);
    $html = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $html);
    
    // Bold and italic
    $html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html);
    $html = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $html);
    
    // Lists - handle unordered lists
    $html = preg_replace_callback('/^- (.+)$/m', function($matches) {
        return '<li>' . trim($matches[1]) . '</li>';
    }, $html);
    
    // Wrap consecutive list items in <ul>
    $html = preg_replace('/(<li>.*<\/li>\n?)+/s', '<ul>$0</ul>', $html);
    
    // Numbered lists
    $html = preg_replace_callback('/^\d+\. (.+)$/m', function($matches) {
        return '<li>' . trim($matches[1]) . '</li>';
    }, $html);
    
    // Wrap consecutive numbered list items in <ol> (but not if already wrapped in <ul>)
    $html = preg_replace_callback('/(<li>.*<\/li>\n?)+/s', function($matches) {
        if (strpos($matches[0], '<ul>') === false) {
            return '<ol>' . $matches[0] . '</ol>';
        }
        return $matches[0];
    }, $html);
    
    // Paragraphs (double newline = new paragraph)
    $html = preg_replace('/\n\n+/', '</p><p>', $html);
    $html = '<p>' . $html . '</p>';
    
    // Clean up empty paragraphs
    $html = preg_replace('/<p><\/p>/', '', $html);
    
    // Remove <p> tags around headings and lists
    $html = preg_replace('/<p>(<h[1-6]>.*<\/h[1-6]>)<\/p>/', '$1', $html);
    $html = preg_replace('/<p>(<ul>.*<\/ul>)<\/p>/s', '$1', $html);
    $html = preg_replace('/<p>(<ol>.*<\/ol>)<\/p>/s', '$1', $html);
    
    // Clean up multiple <p> tags
    $html = preg_replace('/<\/p>\s*<p>/', '<br><br>', $html);
    
    return $html;
}

