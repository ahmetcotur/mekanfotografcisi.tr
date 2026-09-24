<?php
/**
 * Review Service
 * Small helper around the reviews table: inserts a review and keeps
 * freelancer_applications.rating_avg/rating_count in sync (denormalized in PHP
 * rather than a DB trigger, matching this codebase's existing convention).
 */

namespace Core;

class ReviewService
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function addReview($freelancerId, $quoteAssignmentId, $clientUserId, $rating, $comment)
    {
        $review = $this->db->insert('reviews', [
            'freelancer_id' => $freelancerId,
            'quote_assignment_id' => $quoteAssignmentId,
            'client_user_id' => $clientUserId,
            'rating' => $rating,
            'comment' => $comment,
        ]);

        $this->recalculateRating($freelancerId);

        return $review;
    }

    private function recalculateRating($freelancerId)
    {
        $rows = $this->db->query(
            'SELECT COUNT(*) AS cnt, COALESCE(AVG(rating), 0) AS avg_rating FROM reviews WHERE freelancer_id = ? AND is_published = true',
            [$freelancerId]
        );
        $count = (int) ($rows[0]['cnt'] ?? 0);
        $avg = round((float) ($rows[0]['avg_rating'] ?? 0), 2);

        $this->db->update('freelancer_applications', [
            'rating_avg' => $avg,
            'rating_count' => $count,
        ], ['id' => $freelancerId]);
    }
}
