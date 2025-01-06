<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use DOMDocument;
use DOMXPath;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class LinkPreviewService
{
    /**
     * The HTTP client instance.
     */
    private Client $client;

    /**
     * Initialize the service with a configured HTTP client.
     */
    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 10,
            'verify' => false  // Only if needed for development
        ]);
    }

    /**
     * Get preview data for a given URL.
     *
     * @param string $url The URL to fetch preview data from
     * @return array|null Preview data or null if fetching fails
     * @throws Exception For other unexpected errors
     */
    public function getPreviewData(string $url): ?array
    {
        try {
            // Fetch the page content
            $response = $this->client->get($url);
            $html = $response->getBody()->getContents();

            // Initialize DOM parser with proper encoding
            $doc = new DOMDocument('1.0', 'UTF-8');
            // Tell DOMDocument the input is UTF-8
            $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'),
                LIBXML_NOERROR | LIBXML_NOWARNING);

            // Create XPath object for querying the document
            $xpath = new DOMXPath($doc);

            // Extract metadata using different methods
            return [
                'title' => $this->getTitle($xpath),
                'description' => $this->getDescription($xpath),
                'image' => $this->getImage($xpath),
                'url' => $url
            ];

        } catch (GuzzleException $e) {
            Log::error('HTTP client error while fetching link preview:', [
                'error' => $e->getMessage(),
                'url' => $url
            ]);
            return null;

        } catch (Exception $e) {
            Log::error('Error fetching link preview:', [
                'error' => $e->getMessage(),
                'url' => $url
            ]);
            return null;
        }
    }

    /**
     * Extract the title from the page.
     */
    private function getTitle(DOMXPath $xpath): ?string
    {
        // Try OpenGraph title first
        $ogTitle = $this->getMetaContent($xpath, 'og:title');
        if ($ogTitle) return $ogTitle;

        // Fallback to regular title tag
        $titleNode = $xpath->query('//title')->item(0);
        return $titleNode?->nodeValue;
    }

    /**
     * Extract the description from the page.
     */
    private function getDescription(DOMXPath $xpath): ?string
    {
        // Try OpenGraph description first
        $ogDesc = $this->getMetaContent($xpath, 'og:description');
        if ($ogDesc) return $ogDesc;

        // Fallback to meta description
        return $this->getMetaContent($xpath, 'description', 'name');
    }

    /**
     * Extract the image from the page.
     */
    // Find the getImage method and replace it with:
    private function getImage(DOMXPath $xpath): ?string
    {
        // Try OpenGraph image first
        $ogImage = $this->getMetaContent($xpath, 'og:image');
        if ($ogImage && $this->isValidImageUrl($ogImage) && $this->isImageAccessible($ogImage)) {
            return $ogImage;
        }

        // Try Twitter image next
        $twitterImage = $this->getMetaContent($xpath, 'twitter:image', 'name');
        if ($twitterImage && $this->isValidImageUrl($twitterImage) && $this->isImageAccessible($twitterImage)) {
            return $twitterImage;
        }

        // Return null if no valid image found
        return null;
    }

// Add this new method right after getImage:
    private function isValidImageUrl(string $url): bool
    {
        // Basic URL validation
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        // Check if URL points to an image
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower(pathinfo($url, PATHINFO_EXTENSION));

        return in_array($extension, $imageExtensions);
    }

    /**
     * Check if an image URL is actually accessible and returns an image.
     * This method makes a HEAD request to verify the URL exists and returns an image content type.
     */
    private function isImageAccessible(string $url): bool
    {
        try {
            // Make a HEAD request to check the URL without downloading the full image
            $response = $this->client->head($url);

            // Check if response is successful (200-299)
            if ($response->getStatusCode() < 200 || $response->getStatusCode() > 299) {
                return false;
            }

            // Get content type from headers
            $contentType = $response->getHeader('Content-Type');

            // If no content type header, return false
            if (empty($contentType)) {
                return false;
            }

            // Check if content type indicates an image
            $contentType = strtolower($contentType[0]); // Get first content type if multiple
            return str_contains($contentType, 'image/');

        } catch (\Exception $e) {
            // Log the error but don't throw it
            Log::warning('Failed to validate image accessibility:', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Helper method to get meta tag content.
     */
    private function getMetaContent(DOMXPath $xpath, string $property, string $attribute = 'property'): ?string
    {
        $nodes = $xpath->query("//meta[@$attribute='$property']/@content");
        return $nodes->length > 0 ? $nodes->item(0)->nodeValue : null;
    }
}
