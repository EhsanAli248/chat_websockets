<?php

namespace App\Jobs;

use App\Models\Job;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SendJobsToVendors implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public function __construct()
    {
        //
    }


    public function handle(): void
    {
        $vendors = User::role('vendor')->get();
        $jobs = Job::with('user:id,first_name,last_name,country')->get();

        Log::info('Sending jobs to vendors...');

        foreach ($vendors as $vendor) {
            $this->sendJobsToVendor($vendor, $jobs);
        }

        Log::info('Job sending completed.');
    }

    /**
     * Send jobs to a specific vendor.
     *
     * @param  \App\Models\User  $vendor
     * @param  \Illuminate\Database\Eloquent\Collection  $jobs
     */
    private function sendJobsToVendor(User $vendor, $jobs): void
    {
        // Perform the logic to send jobs to the vendor
        Log::info("Sending jobs to vendor: {$vendor->id}");

        // // Example logic:
        // foreach ($jobs as $job) {
        //     // Send the job to the vendor
        // }
    }
}
