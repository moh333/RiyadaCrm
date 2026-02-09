<?php

namespace App\Console\Commands;

use App\Models\Tenant\VtigerScheduledReport;
use App\Modules\Tenant\Reports\Application\Services\ReportGeneratorService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stancl\Tenancy\Facades\Tenancy;
use Stancl\Tenancy\Tenancy as TenancyManager;

class RunScheduledReports extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'reports:run-scheduled 
                            {--tenant= : Specific tenant ID to run reports for}
                            {--force : Force run without checking trigger time}';

    /**
     * The console command description.
     */
    protected $description = 'Run scheduled reports and send them via email';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tenantId = $this->option('tenant');
        $force = $this->option('force');

        if ($tenantId) {
            $this->processForTenant($tenantId, $force);
        } else {
            $this->processAllTenants($force);
        }

        return Command::SUCCESS;
    }

    /**
     * Process scheduled reports for all tenants.
     */
    protected function processAllTenants(bool $force): void
    {
        $tenants = \App\Models\Central\Tenant::all();

        foreach ($tenants as $tenant) {
            $this->info("Processing tenant: {$tenant->id}");

            try {
                $this->processForTenant($tenant->id, $force);
            } catch (\Exception $e) {
                $this->error("Error processing tenant {$tenant->id}: {$e->getMessage()}");
                Log::error("Scheduled Reports Error for tenant {$tenant->id}", [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
    }

    /**
     * Process scheduled reports for a specific tenant.
     */
    protected function processForTenant(string $tenantId, bool $force): void
    {
        tenancy()->initialize($tenantId);

        try {
            $schedules = $this->getSchedulesToRun($force);

            $this->info("Found {$schedules->count()} schedules to process");

            foreach ($schedules as $schedule) {
                $this->processSchedule($schedule);
            }
        } finally {
            tenancy()->end();
        }
    }

    /**
     * Get scheduled reports that are due to run.
     */
    protected function getSchedulesToRun(bool $force)
    {
        $query = VtigerScheduledReport::with('report');

        if (!$force) {
            $query->where('next_trigger_time', '<=', Carbon::now());
        }

        return $query->get();
    }

    /**
     * Process a single scheduled report.
     */
    protected function processSchedule(VtigerScheduledReport $schedule): void
    {
        $report = $schedule->report;

        if (!$report) {
            $this->warn("Report not found for schedule ID: {$schedule->reportid}");
            return;
        }

        $this->info("Processing report: {$report->reportname}");

        try {
            // Generate the report file
            $filePath = $this->generateReportFile($report, $schedule->fileformat ?? 'CSV');

            // Get all recipients
            $recipients = $this->resolveRecipients($schedule);

            // Send email to each recipient
            foreach ($recipients as $email) {
                $this->sendReportEmail($report, $filePath, $email);
            }

            // Update next trigger time
            $schedule->next_trigger_time = $schedule->calculateNextTriggerTime();
            $schedule->save();

            $this->info("Report sent successfully. Next trigger: {$schedule->next_trigger_time}");

            // Clean up generated file
            if (file_exists($filePath)) {
                unlink($filePath);
            }

        } catch (\Exception $e) {
            $this->error("Failed to process report {$report->reportname}: {$e->getMessage()}");
            Log::error("Scheduled Report Error", [
                'report_id' => $report->reportid,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Generate the report file in the specified format.
     */
    protected function generateReportFile($report, string $format): string
    {
        // TODO: Implement full report generation using ReportGeneratorService
        // For now, create a placeholder implementation

        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $extension = strtolower($format) === 'xls' ? 'xlsx' : 'csv';
        $fileName = 'report_' . $report->reportid . '_' . date('Y-m-d_H-i-s') . '.' . $extension;
        $filePath = $tempDir . '/' . $fileName;

        // Placeholder: Create empty file
        // In production, use ReportGeneratorService to generate actual data
        file_put_contents($filePath, "Report: {$report->reportname}\nGenerated: " . now()->toDateTimeString());

        return $filePath;
    }

    /**
     * Resolve all recipient email addresses from the schedule.
     */
    protected function resolveRecipients(VtigerScheduledReport $schedule): array
    {
        $emails = [];

        // Get specific emails
        foreach ($schedule->specific_emails_array as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $email;
            }
        }

        // Resolve recipients
        foreach ($schedule->recipients_array as $recipient) {
            $resolved = $this->resolveRecipient($recipient);
            $emails = array_merge($emails, $resolved);
        }

        return array_unique($emails);
    }

    /**
     * Resolve a single recipient (USER::id, GROUP::id, ROLE::id) to email addresses.
     */
    protected function resolveRecipient(string $recipient): array
    {
        if (!str_contains($recipient, '::')) {
            return [];
        }

        [$type, $id] = explode('::', $recipient, 2);

        return match ($type) {
            'USER' => $this->resolveUserEmail($id),
            'GROUP' => $this->resolveGroupEmails($id),
            'ROLE' => $this->resolveRoleEmails($id),
            default => []
        };
    }

    /**
     * Resolve user email by ID.
     */
    protected function resolveUserEmail(string $userId): array
    {
        $user = \DB::connection('tenant')
            ->table('vtiger_users')
            ->where('id', $userId)
            ->where('deleted', 0)
            ->first();

        return $user && $user->email1 ? [$user->email1] : [];
    }

    /**
     * Resolve all group member emails.
     */
    protected function resolveGroupEmails(string $groupId): array
    {
        $memberIds = \DB::connection('tenant')
            ->table('vtiger_users2group')
            ->where('groupid', $groupId)
            ->pluck('userid');

        $emails = \DB::connection('tenant')
            ->table('vtiger_users')
            ->whereIn('id', $memberIds)
            ->where('deleted', 0)
            ->whereNotNull('email1')
            ->pluck('email1')
            ->toArray();

        return $emails;
    }

    /**
     * Resolve all role member emails.
     */
    protected function resolveRoleEmails(string $roleId): array
    {
        $memberIds = \DB::connection('tenant')
            ->table('vtiger_user2role')
            ->where('roleid', $roleId)
            ->pluck('userid');

        $emails = \DB::connection('tenant')
            ->table('vtiger_users')
            ->whereIn('id', $memberIds)
            ->where('deleted', 0)
            ->whereNotNull('email1')
            ->pluck('email1')
            ->toArray();

        return $emails;
    }

    /**
     * Send the report email.
     */
    protected function sendReportEmail($report, string $filePath, string $email): void
    {
        $this->info("Sending report to: {$email}");

        // Use Laravel's Mail facade to send the email
        Mail::raw(
            "Please find attached the scheduled report: {$report->reportname}\n\nGenerated on: " . now()->toDateTimeString(),
            function ($message) use ($report, $filePath, $email) {
                $message->to($email)
                    ->subject("Scheduled Report: {$report->reportname}")
                    ->attach($filePath);
            }
        );
    }
}
