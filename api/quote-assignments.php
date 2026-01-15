<?php
/**
 * Quote Assignments API
 * Handles assignment of quotes to freelancers
 */

require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/../includes/database.php';

addCorsHeaders();
$user = requireAuth();

$db = new DatabaseClient();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Get assignments for a quote or freelancer
            if (isset($_GET['quote_id'])) {
                $assignments = $db->select('quote_assignments', ['quote_id' => $_GET['quote_id']]);

                // Enrich with freelancer details
                foreach ($assignments as &$assignment) {
                    $freelancer = $db->select('freelancer_applications', ['id' => $assignment['freelancer_id']]);
                    $assignment['freelancer'] = $freelancer[0] ?? null;
                }

                jsonSuccess(['data' => $assignments]);
            } elseif (isset($_GET['freelancer_id'])) {
                $assignments = $db->select('quote_assignments', ['freelancer_id' => $_GET['freelancer_id']]);

                // Enrich with quote details
                foreach ($assignments as &$assignment) {
                    $quote = $db->select('quotes', ['id' => $assignment['quote_id']]);
                    $assignment['quote'] = $quote[0] ?? null;
                }

                jsonSuccess($assignments);
            } else {
                // Get all assignments
                $assignments = $db->query("
                    SELECT qa.*, 
                           f.name as freelancer_name, 
                           f.email as freelancer_email,
                           f.city as freelancer_city,
                           q.name as customer_name,
                           q.location as quote_location,
                           q.service as quote_service
                    FROM quote_assignments qa
                    LEFT JOIN freelancer_applications f ON qa.freelancer_id = f.id
                    LEFT JOIN quotes q ON qa.quote_id = q.id
                    ORDER BY qa.assigned_at DESC
                ");
                jsonSuccess($assignments);
            }
            break;

        case 'POST':
            // Create new assignment
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['quote_id']) || empty($data['freelancer_id'])) {
                jsonError('quote_id and freelancer_id are required', 400);
            }

            // Check if assignment already exists
            $existing = $db->select('quote_assignments', [
                'quote_id' => $data['quote_id'],
                'freelancer_id' => $data['freelancer_id']
            ]);

            if (!empty($existing)) {
                jsonError('Bu freelancer zaten bu teklife atanmış', 400);
            }

            $assignmentData = [
                'quote_id' => $data['quote_id'],
                'freelancer_id' => $data['freelancer_id'],
                'status' => $data['status'] ?? 'pending',
                'assigned_by' => $user['id'] ?? null,
                'admin_note' => $data['admin_note'] ?? null
            ];

            $result = $db->insert('quote_assignments', $assignmentData);
            jsonSuccess($result);
            break;

        case 'PUT':
            // Update assignment status
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['id'])) {
                jsonError('Assignment ID is required', 400);
            }

            $updateData = [];
            if (isset($data['status']))
                $updateData['status'] = $data['status'];
            if (isset($data['freelancer_note']))
                $updateData['freelancer_note'] = $data['freelancer_note'];
            if (isset($data['admin_note']))
                $updateData['admin_note'] = $data['admin_note'];
            $updateData['updated_at'] = date('Y-m-d H:i:s');

            $result = $db->update('quote_assignments', $updateData, ['id' => $data['id']]);
            jsonSuccess($result);
            break;

        case 'DELETE':
            // Delete assignment
            if (empty($_GET['id'])) {
                jsonError('Assignment ID is required', 400);
            }

            $result = $db->delete('quote_assignments', ['id' => $_GET['id']]);
            jsonSuccess(['deleted' => true]);
            break;

        default:
            jsonError('Method not allowed', 405);
    }

} catch (Exception $e) {
    error_log("Quote assignments error: " . $e->getMessage());
    jsonError('Failed to process request: ' . $e->getMessage(), 500);
}
