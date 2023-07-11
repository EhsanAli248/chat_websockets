<?php

namespace App\Http\Controllers;

use App\Models\Bid;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function PHPUnit\Framework\isEmpty;

class BidController extends Controller
{
    //submit bid by vendor
    public function submitBid(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['vendor', 'admin']) || !$user->hasPermissionTo('submit-bid')) {
            return response()->json([
                'status' => false,
                'message' => 'Sorry! you are not authorized user'
            ], 403);
        }
        if ($user->hasAnyRole(['vendor', 'admin']) && $user->hasPermissionTo('submit-bid')) {
            $validatedData = $request->validate([
                'purposal_detail' => 'required',
                'price' => 'required',
                'timeline' => 'required',
                'boost_level' => 'required',
            ]);
            $job = Job::find($id);

            if (!$job) {
                return response()->json([
                    'status' => false,
                    'message' => 'sorry you are bidding against a job that did not exist',
                ], 404);
            }
            $existingBid = Bid::where('user_id', $user->id)->where('job_id', $job->id)->exists();
            if($existingBid){
                return response()->json([
                    'status' => false,
                    'message' => 'you have already submitted the bid on this job'
                ], 201);
            }
            $bid = Bid::create([
                'user_id' => $user->id,
                'job_id' => $job->id,
                'purposal_detail' => $validatedData['purposal_detail'],
                'price' =>  $validatedData['price'],
                'timeline' =>  $validatedData['timeline'],
                'boost_level' => $validatedData['boost_level'],
            ]);
            return response()->json([
                'status' => 'ok',
                'message' => 'bid submitted successfully',
                'bid' => $bid,
            ], 200);
        }
    }

    // to update the bid by vendor
    public function updateBid(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['vendor', 'admin']) || !$user->hasPermissionTo('edit-bid')) {
            return response()->json([
                'status' => false,
                'message' => 'sorry! you are not authorized user to update this job',
            ], 403);
        }
        $bid = Bid::find($id);
        if (!$bid) {
            return response()->json([
                'status' => false,
                'message' => 'sorry! bid did not exist',
            ], 201);
        }
        if ($bid->user_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Sorry! you are not authorized user to update this bid',

            ], 403);
        }
        $validatedData = $request->validate([
            'purposal_detail' => 'required',
            'price' => 'required',
            'timeline' => 'required',
            'boost_level' => 'required',

        ]);
        $bid->update($validatedData);
        //update method is just update the data, while fresh method fetch the
        //updated data from the database.
        $bidData = $bid->fresh();
        return response()->json([
            'status' => 'ok',
            'message' => 'you have updated your bid successfully',
            'bid_updated_data' => $bidData,

        ], 200);
    }

    //to delete the bid by vendor
    public function deleteBid(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['vendor', 'admin']) || !$user->hasPermissionTo('withdraw-bid')) {
            return response()->json([
                'status' => false,
                'message' => 'sorry! you are not authorized user to delete this bid',
            ], 403);
        }

        $bid = Bid::find($id);
        if (!$bid) {
            return response()->json([
                'status' => false,
                'message' => 'sorry! bid did not exist',
            ], 201);
        }
        if ($bid->user_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'sorry! you are not valid user to delete this bid',
            ], 403);
        }
        $bid->delete();
        return response()->json([
            'status' => 'ok',
            'message' => 'you have withdraw your bid successfully',
        ], 200);
    }

    //to show bids detail to that vendor that has submitted on different jobs
    public function showVendorBids(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['vendor', 'admin']) || !$user->hasPermissionTo('view-bid')) {
            return response()->json([
                'status' => false,
                'message' => 'sorry! you are not authorized user',

            ], 403);
        }
        $bids = Bid::where('user_id', $user->id)->get();
        return response()->json([
            'status' => 'ok',
            'message' => 'your all submitted bids detail is here',
            'bid_detail' => $bids
        ], 200);
    }

    // to show all bids to client on his uploaded job
    public function showJobBids(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['client', 'admin']) || !$user->hasPermissionTo('view-bid')) {
            return response()->json([
                'status' => false,
                'message' => 'sorry ! you are not authorized user to see the bids',
            ], 403);
        }
        $job = Job::find($id);
        $bid = Bid::where('job_id',$id)->first();
        if (!$job) {
            return response()->json([
                'status' => 'ok',
                'message' => 'sorry job did not exist'
            ], 404);
        }
        if (!$bid) {
            return response()->json([
                'status' => false,
                'message' => 'sorry! bid did not exist',
            ], 404);
        }
        $bids = $job->bids;
        if ($user->hasRole('client') && $job->user_id !== $user->id)
            return response()->json([
                'status' => false,
                'message' => 'you are not authorized to view this job',

            ], 403);
        $bids = $job->bids;
        return response()->json([
            'status' => 'ok',
            'message' => 'all bids on your uploaded job is here',
            'bids' => $bids,
        ], 200);
    }
}
