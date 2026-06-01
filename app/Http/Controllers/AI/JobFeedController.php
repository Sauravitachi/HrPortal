<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Services\JobFeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class JobFeedController extends Controller
{
    public function __construct(protected JobFeedService $feedService) {}

    /**
     * Render public XML jobs feed.
     */
    public function feedXml(): Response
    {
        $xml = $this->feedService->generateXmlFeed();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    /**
     * Render public JSON jobs feed.
     */
    public function feedJson(): JsonResponse
    {
        $json = $this->feedService->generateJsonFeed();

        return response()->json($json);
    }

    /**
     * Render public RSS jobs feed.
     */
    public function feedRss(): Response
    {
        $rss = $this->feedService->generateRssFeed();

        return response($rss, 200, [
            'Content-Type' => 'application/rss+xml; charset=UTF-8',
        ]);
    }
}
