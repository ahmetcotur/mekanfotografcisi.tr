<?php
/**
 * Freelancer Matcher Service
 * Matches quotes with suitable freelancers based on location and specialization
 */

namespace Core;

class FreelancerMatcher
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Find matching freelancers for a quote
     * 
     * @param string $quoteLocation Location from quote (e.g., "İstanbul", "Ankara/Çankaya")
     * @param string $serviceType Service type from quote
     * @return array Ranked list of matching freelancers with scores
     */
    public function findMatchingFreelancers($quoteLocation, $serviceType = null)
    {
        // Get all approved freelancers
        $freelancers = $this->db->select('freelancer_applications', ['status' => 'approved']);

        if (empty($freelancers)) {
            return [];
        }

        // Parse quote location
        $locationParts = $this->parseLocation($quoteLocation);

        $rankedFreelancers = [];

        foreach ($freelancers as $freelancer) {
            $score = 0;
            $matchDetails = [];

            // 1. Check location match
            $locationScore = $this->calculateLocationScore($freelancer, $locationParts);
            $score += $locationScore['score'];
            $matchDetails['location'] = $locationScore['details'];

            // 2. Check specialization match
            if ($serviceType) {
                $specializationScore = $this->calculateSpecializationScore($freelancer, $serviceType);
                $score += $specializationScore['score'];
                $matchDetails['specialization'] = $specializationScore['details'];
            }

            // 3. Experience bonus
            $experienceScore = $this->calculateExperienceScore($freelancer);
            $score += $experienceScore;
            $matchDetails['experience'] = $experienceScore;

            if ($score > 0) {
                $rankedFreelancers[] = [
                    'freelancer' => $freelancer,
                    'score' => $score,
                    'match_details' => $matchDetails
                ];
            }
        }

        // Sort by score descending
        usort($rankedFreelancers, function ($a, $b) {
            return $b['score'] - $a['score'];
        });

        return $rankedFreelancers;
    }

    /**
     * Find open marketplace quotes ranked for a specific freelancer (the inverse
     * of findMatchingFreelancers): used by the freelancer's "open jobs" dashboard.
     *
     * "Open" means: visible to the marketplace (not a direct-to-photographer
     * request), still in an actionable status, and not already accepted or
     * completed by someone (a quote can have multiple pending self-claims, but
     * once one is accepted/completed it drops out of everyone's open list).
     *
     * @param int $freelancerId freelancer_applications.id
     * @return array Ranked list of matching quotes with scores
     */
    public function findMatchingQuotesForFreelancer($freelancerId)
    {
        $freelancers = $this->db->select('freelancer_applications', ['id' => $freelancerId]);
        if (empty($freelancers)) {
            return [];
        }
        $freelancer = $freelancers[0];

        $openQuotes = $this->db->select('quotes', [
            'visibility' => 'marketplace',
            'in.status' => ['beklemede', 'inceleniyor'],
        ]);

        if (empty($openQuotes)) {
            return [];
        }

        // Drop quotes that already have an accepted/completed assignment
        // (to anyone), and quotes this freelancer already has a pending
        // self-claim on (no point re-showing it as "open").
        $lockedQuoteIds = [];
        $ownPendingQuoteIds = [];
        $allAssignments = $this->db->select('quote_assignments', []);
        foreach ($allAssignments as $assignment) {
            if (in_array($assignment['status'], ['accepted', 'completed'], true)) {
                $lockedQuoteIds[$assignment['quote_id']] = true;
            }
            if ((int) $assignment['freelancer_id'] === (int) $freelancerId && $assignment['status'] === 'pending') {
                $ownPendingQuoteIds[$assignment['quote_id']] = true;
            }
        }

        $ranked = [];
        foreach ($openQuotes as $quote) {
            if (isset($lockedQuoteIds[$quote['id']]) || isset($ownPendingQuoteIds[$quote['id']])) {
                continue;
            }

            $locationParts = $this->parseLocation($quote['location']);
            $score = 0;
            $matchDetails = [];

            $locationScore = $this->calculateLocationScore($freelancer, $locationParts);
            $score += $locationScore['score'];
            $matchDetails['location'] = $locationScore['details'];

            if (!empty($quote['service'])) {
                $specializationScore = $this->calculateSpecializationScore($freelancer, $quote['service']);
                $score += $specializationScore['score'];
                $matchDetails['specialization'] = $specializationScore['details'];
            }

            if ($score > 0) {
                $ranked[] = [
                    'quote' => $quote,
                    'score' => $score,
                    'match_details' => $matchDetails,
                ];
            }
        }

        usort($ranked, function ($a, $b) {
            return $b['score'] - $a['score'];
        });

        return $ranked;
    }

    /**
     * Parse location string into province and district
     */
    private function parseLocation($location)
    {
        if (empty($location)) {
            return ['province' => null, 'district' => null];
        }

        $parts = explode('/', $location);
        $province = trim($parts[0]);
        $district = isset($parts[1]) ? trim($parts[1]) : null;

        // Try to find province in database
        $provinceData = $this->db->select('locations_province', ['name' => $province]);
        if (empty($provinceData)) {
            // Try slug match
            $slug = $this->slugify($province);
            $provinceData = $this->db->select('locations_province', ['slug' => $slug]);
        }

        $provinceId = !empty($provinceData) ? $provinceData[0]['id'] : null;
        $districtId = null;

        if ($district && $provinceId) {
            $districtData = $this->db->select('locations_district', [
                'name' => $district,
                'province_id' => $provinceId
            ]);
            if (empty($districtData)) {
                $slug = $this->slugify($district);
                $districtData = $this->db->select('locations_district', [
                    'slug' => $slug,
                    'province_id' => $provinceId
                ]);
            }
            $districtId = !empty($districtData) ? $districtData[0]['id'] : null;
        }

        return [
            'province' => $province,
            'province_id' => $provinceId,
            'district' => $district,
            'district_id' => $districtId
        ];
    }

    /**
     * Calculate location match score
     */
    private function calculateLocationScore($freelancer, $locationParts)
    {
        $score = 0;
        $details = 'No match';

        // Check city field (legacy)
        if (!empty($freelancer['city']) && !empty($locationParts['province'])) {
            if (
                stripos($freelancer['city'], $locationParts['province']) !== false ||
                stripos($locationParts['province'], $freelancer['city']) !== false
            ) {
                $score += 50;
                $details = 'City match';
            }
        }

        // Check working_regions (new structured data)
        if (!empty($freelancer['working_regions'])) {
            $regions = json_decode($freelancer['working_regions'], true);

            if (is_array($regions)) {
                foreach ($regions as $region) {
                    // Province match
                    if (isset($region['province_id']) && $region['province_id'] == $locationParts['province_id']) {
                        $score += 100;
                        $details = 'Province match';

                        // District match (bonus)
                        if (!empty($locationParts['district_id']) && !empty($region['district_ids'])) {
                            if (in_array($locationParts['district_id'], $region['district_ids'])) {
                                $score += 50;
                                $details = 'District match';
                            }
                        }
                        break;
                    }
                }
            }
        }

        return ['score' => $score, 'details' => $details];
    }

    /**
     * Calculate specialization match score
     */
    private function calculateSpecializationScore($freelancer, $serviceType)
    {
        $score = 0;
        $details = 'No match';

        if (empty($freelancer['specialization'])) {
            return ['score' => 0, 'details' => $details];
        }

        $specializations = json_decode($freelancer['specialization'], true);
        if (!is_array($specializations)) {
            return ['score' => 0, 'details' => $details];
        }

        // Service type mapping
        $serviceMap = [
            'mimari' => ['mimari', 'ic-mekan'],
            'ic-mekan' => ['ic-mekan', 'mimari'],
            'otel' => ['otel'],
            'emlak' => ['emlak', 'mimari'],
            'yemek' => ['yemek'],
            'drone' => ['drone']
        ];

        $matchTypes = $serviceMap[$serviceType] ?? [$serviceType];

        foreach ($specializations as $spec) {
            if (in_array($spec, $matchTypes)) {
                $score += 30;
                $details = 'Specialization match: ' . $spec;
                break;
            }
        }

        return ['score' => $score, 'details' => $details];
    }

    /**
     * Calculate experience score
     */
    private function calculateExperienceScore($freelancer)
    {
        if (empty($freelancer['experience'])) {
            return 0;
        }

        $experienceMap = [
            '0-1' => 5,
            '1-3' => 10,
            '3-5' => 15,
            '5-10' => 20,
            '10+' => 25
        ];

        return $experienceMap[$freelancer['experience']] ?? 0;
    }

    /**
     * Simple slugify function
     */
    private function slugify($text)
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }
}
