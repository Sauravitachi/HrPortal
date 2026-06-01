<?php

namespace App\Services;

use App\Models\JobFeedLog;
use App\Models\JobPost;
use Illuminate\Support\Facades\Request;

class JobFeedService
{
    /**
     * Get active jobs for external consumption.
     */
    protected function getActiveJobs()
    {
        return JobPost::with(['department', 'jobCategory', 'tenant.company'])
            ->where('status', 'Active')
            ->latest()
            ->get();
    }

    /**
     * Generate dynamic XML feed.
     */
    public function generateXmlFeed(): string
    {
        $this->logFeedAccess('xml');
        $jobs = $this->getActiveJobs();

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><jobs></jobs>');

        foreach ($jobs as $job) {
            $item = $xml->addChild('job');
            $item->addChild('id', $job->id);
            $item->addChild('title', htmlspecialchars($job->title));
            $item->addChild('department', htmlspecialchars($job->department->name ?? ''));
            $item->addChild('experience', htmlspecialchars($job->experience_required));
            $item->addChild('salary', htmlspecialchars($job->salary_range));
            $item->addChild('description', htmlspecialchars(strip_tags($job->description)));
            $item->addChild('company', htmlspecialchars($job->tenant->name ?? 'HrPortal Tenant'));
            $item->addChild('location', htmlspecialchars($job->tenant->company->location ?? 'Onsite'));

            // Build absolute application link
            $applyUrl = route('careers.show', $job->id);
            $item->addChild('apply_url', htmlspecialchars($applyUrl));
            $item->addChild('published_at', $job->created_at->toRssString());
        }

        return $xml->asXML();
    }

    /**
     * Generate dynamic JSON feed.
     */
    public function generateJsonFeed(): array
    {
        $this->logFeedAccess('json');
        $jobs = $this->getActiveJobs();

        $feed = [
            'version' => 'https://jsonfeed.org/version/1.1',
            'title' => 'HrPortal AI Job Postings Feed',
            'home_page_url' => url('/'),
            'feed_url' => route('jobs.feed.json'),
            'items' => [],
        ];

        foreach ($jobs as $job) {
            $feed['items'][] = [
                'id' => $job->id,
                'title' => $job->title,
                'summary' => strip_tags($job->description),
                'content_text' => strip_tags($job->description),
                'url' => route('careers.show', $job->id),
                'date_published' => $job->created_at->toIso8601String(),
                'authors' => [
                    ['name' => $job->tenant->name ?? 'HrPortal Company'],
                ],
                'attachments' => [],
                '_extra_metadata' => [
                    'department' => $job->department->name ?? '',
                    'experience_required' => $job->experience_required,
                    'salary_range' => $job->salary_range,
                    'location' => $job->tenant->company->location ?? 'Onsite',
                ],
            ];
        }

        return $feed;
    }

    /**
     * Generate dynamic RSS feed.
     */
    public function generateRssFeed(): string
    {
        $this->logFeedAccess('rss');
        $jobs = $this->getActiveJobs();

        $rss = '<?xml version="1.0" encoding="UTF-8" ?>';
        $rss .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
        $rss .= '<channel>';
        $rss .= '<title>'.htmlspecialchars('HrPortal AI Job Postings').'</title>';
        $rss .= '<link>'.htmlspecialchars(url('/')).'</link>';
        $rss .= '<description>'.htmlspecialchars('Stay updated with open career opportunities in our HRMS system.').'</description>';
        $rss .= '<language>en-us</language>';

        foreach ($jobs as $job) {
            $rss .= '<item>';
            $rss .= '<title>'.htmlspecialchars($job->title).'</title>';
            $rss .= '<link>'.htmlspecialchars(route('careers.show', $job->id)).'</link>';
            $rss .= '<description>'.htmlspecialchars(strip_tags($job->description)).'</description>';
            $rss .= '<pubDate>'.$job->created_at->toRssString().'</pubDate>';
            $rss .= '<guid>'.route('careers.show', $job->id).'</guid>';
            $rss .= '</item>';
        }

        $rss .= '</channel>';
        $rss .= '</rss>';

        return $rss;
    }

    /**
     * Audit log of feed telemetry.
     */
    protected function logFeedAccess(string $feedType): void
    {
        $tenantId = null;
        if (app()->bound('currentTenant')) {
            $tenant = app('currentTenant');
            if ($tenant) {
                $tenantId = $tenant->id;
            }
        }

        JobFeedLog::create([
            'tenant_id' => $tenantId,
            'feed_type' => $feedType,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'accessed_at' => now(),
        ]);
    }
}
