<?php

namespace Core;

class PexelsService
{
    private $apiKey;
    private $profileName;
    private $cacheFile;
    private $seedsFile;
    private $cacheDuration = 86400; // 24 hours

    public function __construct()
    {
        $this->apiKey = $_ENV['PEXELS_API_KEY'] ?? '';
        // Profile ID: 776571149, Name: Ahmet ÇÖTÜR
        $this->profileName = $_ENV['PEXELS_PROFILE_NAME'] ?? '776571149';
        $this->cacheFile = __DIR__ . '/../../data/pexels_cache.json';
        $this->seedsFile = __DIR__ . '/../../data/pexels_seeds.json';
    }

    /**
     * Get a random photo from the profile
     */
    public function getRandomPhoto()
    {
        $photos = $this->getPhotos();
        if (empty($photos)) {
            return null;
        }

        return $photos[array_rand($photos)];
    }

    /**
     * Get a batch of random photos
     */
    public function getRandomPhotosBatch($count = 3)
    {
        $photos = $this->getPhotos();
        if (empty($photos)) {
            return [];
        }

        if (count($photos) <= $count) {
            return $photos;
        }

        $keys = array_rand($photos, $count);
        $result = [];
        foreach ((array) $keys as $key) {
            $result[] = $photos[$key];
        }
        return $result;
    }

    /**
     * Get random photos (alias for getRandomPhotosBatch)
     */
    public function getRandomPhotos($count = 3)
    {
        return $this->getRandomPhotosBatch($count);
    }


    /**
     * Fetch photos from Pexels API or cache
     */
    public function getPhotos()
    {
        // Check cache first
        if (file_exists($this->cacheFile) && (time() - filemtime($this->cacheFile) < $this->cacheDuration)) {
            $cache = json_decode(file_get_contents($this->cacheFile), true);
            if (!empty($cache)) {
                return $cache;
            }
        }

        return $this->refreshPhotos();
    }

    /**
     * Refresh photos from Pexels API
     */
    public function refreshPhotos()
    {
        if (empty($this->apiKey)) {
            return [];
        }

        $allPhotos = [];

        // Step 1: Discover via Seeds (New IDs found from browser extraction)
        if (file_exists($this->seedsFile)) {
            $seeds = json_decode(file_get_contents($this->seedsFile), true) ?: [];

            // Load existing to skip found ones
            $existingIds = [];
            if (file_exists($this->cacheFile)) {
                $existing = json_decode(file_get_contents($this->cacheFile), true) ?: [];
                foreach ($existing as $p)
                    $existingIds[] = $p['id'];
            }

            $toFetch = [];
            foreach ($seeds as $id) {
                if (!in_array($id, $existingIds)) {
                    $toFetch[] = $id;
                }
                if (count($toFetch) >= 40)
                    break; // Fetch in batches
            }

            foreach ($toFetch as $id) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://api.pexels.com/v1/photos/{$id}");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: {$this->apiKey}"]);
                $res = curl_exec($ch);
                curl_close($ch);

                $p = json_decode($res, true);
                if (isset($p['id'])) {
                    $allPhotos[$p['id']] = [
                        'id' => $p['id'],
                        'url' => $p['url'],
                        'src' => $p['src']['large'] ?? $p['src']['original'],
                        'thumbnail' => $p['src']['medium'],
                        'photographer' => $p['photographer'],
                        'alt' => $p['alt'] ?? ''
                    ];
                }
            }
        }

        // Step 2: Broad search queries (Stay as fallback for new uploads)
        $searchTerms = ['Ahmet Çötür', 'Ahmet ÇÖTÜR', 'Ahmet Cotur', 'Ahmet Cötür'];
        // $allPhotos = []; // This line was removed as it's initialized once at the top of the function.

        foreach ($searchTerms as $term) {
            $query = urlencode($term);
            $url = "https://api.pexels.com/v1/search?query={$query}&per_page=80";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: {$this->apiKey}"]);
            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($response, true);

            if (isset($data['photos'])) {
                foreach ($data['photos'] as $photo) {
                    $pName = $photo['photographer'] ?? '';
                    // Strict filtering to ensure only results from this exact person
                    if (
                        stripos($pName, 'Ahmet') !== false &&
                        (stripos($pName, 'Cotur') !== false ||
                            stripos($pName, 'Çötür') !== false ||
                            stripos($pName, 'ÇÖTÜR') !== false)
                    ) {

                        $allPhotos[$photo['id']] = [
                            'id' => $photo['id'],
                            'url' => $photo['url'],
                            'src' => $photo['src']['large'] ?? $photo['src']['original'],
                            'thumbnail' => $photo['src']['medium'],
                            'photographer' => $pName,
                            'alt' => $photo['alt'] ?? ''
                        ];
                    }
                }
            }

            if (count($allPhotos) >= 10)
                break;
        }

        // Final fallback: Use the specific IDs if found to be missing
        if (empty($allPhotos)) {
            $knownIds = ['19061699', '29702291'];
            foreach ($knownIds as $id) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://api.pexels.com/v1/photos/{$id}");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: {$this->apiKey}"]);
                $res = curl_exec($ch);
                curl_close($ch);
                $p = json_decode($res, true);
                if (isset($p['id'])) {
                    $allPhotos[$p['id']] = [
                        'id' => $p['id'],
                        'url' => $p['url'],
                        'src' => $p['src']['large'] ?? $p['src']['original'],
                        'thumbnail' => $p['src']['medium'],
                        'photographer' => $p['photographer'],
                        'alt' => $p['alt'] ?? ''
                    ];
                }
            }
        }

        // Step 3: Load existing photos for merging/accumulation
        $existingPhotos = [];
        if (file_exists($this->cacheFile)) {
            $existingPhotos = json_decode(file_get_contents($this->cacheFile), true) ?: [];
        }

        // Convert existing photos to an associative array by ID for fast merging
        foreach ($existingPhotos as $photo) {
            if (isset($photo['id'])) {
                $allPhotos[$photo['id']] = $photo;
            }
        }

        $photos = array_values($allPhotos);

        // Save to persistent storage if we have any photos
        if (!empty($photos)) {
            if (!is_dir(dirname($this->cacheFile))) {
                mkdir(dirname($this->cacheFile), 0755, true);
            }
            file_put_contents($this->cacheFile, json_encode($photos));
        }

        return $photos;
    }
}
