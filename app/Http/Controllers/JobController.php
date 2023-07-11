<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Auth\Events\Validated;

class JobController extends Controller
{

    // get all jobs by vendor that is created by clients , as well as a valid client can
    //see all his created jobs
    public function getUserJobs()
    {
        $user = Auth::user();
        if ($user->hasAnyRole(['vendor', 'admin']) && $user->hasPermissionTo('view-job')) {
            $jobs = Job::with('user:id,first_name,last_name,country')->get();
            return response()->json([
                'status' => 'ok',
                'message' => 'here are all uploaded jobs',
                'all_jobs' => $jobs,
            ], 200);
        } else if (!$user->hasPermissionTo('view-job')) {
            return response()->json([
                'status' => false,
                'message' => 'you are not a authorized user',
            ], 403);
        }
        $jobs = $user->jobs;
        return response()->json([
            'status' => 'ok',
            'message' => 'here is all your uploaded jobs detail',
            'jobs_detail' => $jobs,
        ], 200);
    }



    //to create/upload jobs by client
    public function createJob(Request $request)
    {

        if (!Auth::user()->hasPermissionTo('create-job')) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to create a job',
            ], 403);
        }

        $validatedData = $request->validate([
            'title' => 'required',
            'description' => 'required',
            'price_type' => 'required',
            'duration' => 'required',
            'skills' => 'required|array',
        ]);

        $priceType = $validatedData['price_type']['option'];

        if($validatedData['price_type']['hourlyRate']['from']) {
           $price_type='hourly';
           $hourlyRate = $validatedData['price_type']['hourlyRate'];
           $minPrice = $hourlyRate['from'];
           $maxPrice = $hourlyRate['to'];
       } else if ($priceType === 'project') {
           $price_type='fixed';  
           $minPrice = $validatedData['price_type']['projectBudget'];
           $maxPrice = $minPrice;
       }
       $skills = isset($validatedData['skills']) ? json_encode($validatedData['skills']) : '[]';

        $job = Job::create([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'price_type' => $price_type,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'duration' => $validatedData['duration'],
            'skills' => $skills,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Job uploaded successfully',
            'job_detail' => $job,
        ], 200);
    }

    //to update uploaded job by valid client
    public function updateJob(Request $request,$id)
    {
        $job = Job::find($id);
        if (!$job) {
            return response()->json([
                'status' => false,
                'message' => 'job did not exit',
            ], 201);
        } else if ($job->user_id != Auth::id() || !Auth::user()->hasPermissionTo('edit-job')) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to update this job',
            ], 403);
        }
        $validatedData = $request->validate([
            'title' => 'required',
            'description' => 'required',
            'price_type' => 'required',
            'duration' => 'required',
            'skills' => 'required|array',
        ]);

        $priceType = $validatedData['price_type']['option'];

        if($validatedData['price_type']['hourlyRate']['from']) {
           $price_type='hourly';
           $hourlyRate = $validatedData['price_type']['hourlyRate'];
           $minPrice = $hourlyRate['from'];
           $maxPrice = $hourlyRate['to'];
       } else if ($priceType === 'project') {
           $price_type='fixed';
           $minPrice = $validatedData['price_type']['projectBudget'];
           $maxPrice = $minPrice;
       }
       $skills = isset($validatedData['skills']) ? json_encode($validatedData['skills']) : '[]';
        $job->update([
            'title'=>$validatedData['title'],
            'description'=>$validatedData['description'],
            'price_type'=>$price_type,
            'min_price'=>$minPrice,
            'max_price'=>$maxPrice,
            'duration'=>$validatedData['duration'],
            'skills'=>$skills,
        ]);
        $jobData=$job->fresh();
        return response()->json([
            'status'=>'ok',
            'message'=>'job updated successfully',
            'update_job'=>$jobData,
        ],200);

    }

    //to delete jobs by valid client,
    public function deleteJob(Request $request, $id)
    {
        $job = Job::find($id);
        $user = Auth::user();
        if (!$job) {
            return response()->json([
                'status' => false,
                'message' => 'job did not exist'
            ], 403);
        }

        if ($job->user_id !== $user->id || !$user->hasPermissionTo('delete-job')) {
            return response()->json([
                'status' => false,
                'message' => 'sorry! you are not authorized user',
            ], 403);
        }
        $job->delete();
        return response()->json([
            'status' => 'ok',
            'message' => 'you have deleted your uploaded job',
        ], 200);
    }
}
