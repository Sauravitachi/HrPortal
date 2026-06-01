<?php

namespace App\Console\Commands;

use App\Models\JobFeedLog;
use App\Models\JobPublishing;
use App\Models\ResumeParseLog;
use Illuminate\Console\Command;

class RecruitmentLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recruitment:logs 
                            {--type=all : The type of logs to display (all, parser, feed, publish)} 
                            {--limit=10 : The maximum number of rows to retrieve}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display active telemetry and transaction logs of the AI Recruitment Suite';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->option('type');
        $limit = intval($this->option('limit'));

        $this->info('=================================================');
        $this->info('   AI RECRUITMENT SUITE AUDIT & TELEMETRY LOGS   ');
        $this->info('=================================================');

        if (in_array($type, ['all', 'parser'])) {
            $this->displayParserLogs($limit);
        }

        if (in_array($type, ['all', 'publish'])) {
            $this->displayPublishingLogs($limit);
        }

        if (in_array($type, ['all', 'feed'])) {
            $this->displayFeedLogs($limit);
        }

        return Command::SUCCESS;
    }

    /**
     * Display resume parser status logs.
     */
    protected function displayParserLogs(int $limit): void
    {
        $this->newLine();
        $this->comment(">> Resume Parser Logs (Latest {$limit})");

        $logs = ResumeParseLog::with('candidateApplication')
            ->latest()
            ->take($limit)
            ->get();

        if ($logs->isEmpty()) {
            $this->warn('No resume parsing logs found.');

            return;
        }

        $headers = ['Log ID', 'Candidate Name', 'Status', 'Error Message', 'Timestamp'];
        $data = [];

        foreach ($logs as $l) {
            $data[] = [
                $l->id,
                $l->candidateApplication->full_name ?? 'Unknown',
                strtoupper($l->status),
                $l->error_message ?? '-',
                $l->created_at->format('Y-m-d H:i:s'),
            ];
        }

        $this->table($headers, $data);
    }

    /**
     * Display job board publishing transaction logs.
     */
    protected function displayPublishingLogs(int $limit): void
    {
        $this->newLine();
        $this->comment(">> Job Board Publishing logs (Latest {$limit})");

        $logs = JobPublishing::with('jobPost')
            ->latest()
            ->take($limit)
            ->get();

        if ($logs->isEmpty()) {
            $this->warn('No job publishing transaction logs found.');

            return;
        }

        $headers = ['ID', 'Job Post Title', 'Platform', 'Status', 'Error Message', 'Timestamp'];
        $data = [];

        foreach ($logs as $l) {
            $data[] = [
                $l->id,
                $l->jobPost->title ?? 'Unknown',
                strtoupper($l->platform),
                strtoupper($l->status),
                $l->error_message ?? '-',
                $l->created_at->format('Y-m-d H:i:s'),
            ];
        }

        $this->table($headers, $data);
    }

    /**
     * Display job feed crawler logs.
     */
    protected function displayFeedLogs(int $limit): void
    {
        $this->newLine();
        $this->comment(">> Job Feed Crawler Access logs (Latest {$limit})");

        $logs = JobFeedLog::latest()
            ->take($limit)
            ->get();

        if ($logs->isEmpty()) {
            $this->warn('No job feed access logs found.');

            return;
        }

        $headers = ['ID', 'Feed Format', 'IP Address', 'User Agent Summary', 'Accessed At'];
        $data = [];

        foreach ($logs as $l) {
            $data[] = [
                $l->id,
                strtoupper($l->feed_type),
                $l->ip_address ?? '127.0.0.1',
                substr($l->user_agent ?? 'Unknown', 0, 50).'...',
                $l->accessed_at ? $l->accessed_at->format('Y-m-d H:i:s') : $l->created_at->format('Y-m-d H:i:s'),
            ];
        }

        $this->table($headers, $data);
    }
}
