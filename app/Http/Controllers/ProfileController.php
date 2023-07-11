<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    use SoftDeletes;
    public function showProfile($id)
    {

        $user = User::with(['vendor', 'client'])->find($id);
        if ($user->vendor) {
            return response()->json([
                'status' => 'ok',
                'message' => 'here is vendor data',
                'data' => $user,
            ], 200);
        } else {
            $profile = $user;
            $type = 'user';
            return response()->json([
                'status' => 'ok',
                'message' => 'here is client data',
                'data' => $profile,
            ], 200);
        }
    }

    // public function updateProfile(Request $request, $id)
    // {
    //     $user=User::find($id);
    //     if(!$user){
    //         return response()->json([
    //             'status'=>'false',
    //             'message'=>'user not found',

    //         ],404);
    //     }
    //     $user->update($request->except('is_active'));
    //     if($request->has('is_active')){
    //         $isActive=$request->input('is_active');
    //         if($isActive==0)
    //         $user->delete();
    //         return response()->json([
    //             'status'=>'ok',
    //             'message'=>'profile is deactivated successfully',

    //         ]);
    //     }
    //     return response()->json([
    //         'status'=>'ok',
    //         'message'=>'user profile updated successfully',
    //         'data'=>$user,
    //     ],200);

    // }

    public function updateProfile(Request $request,$id){
        $user=User::with(['client','vendor'])->find($id);
        if($user->vendor){
        $updatedData=$user->vendor->update([
            'is_verified'=>$request->input('is_verified'),
            'rating'=>$request->input('rating'),
            'description'=>$request->input('description'),
            'skills'=>$request->input('skills'),
            'user_id'=>$user->id,
            // 'user_id'=>Auth()::user()->id,

            ]);
            return response()->json([
                'status'=>'ok',
                'message'=>'vendor profile updated successfully',
                'data'=>$updatedData,
            ],200);
        }
       else if($user->client){
            $updatedData=$user->client->update([
                'is_verified'=>$request->input('is_verified'),
                'rating'=>$request->input('rating'),
                'description'=>$request->input('description'),
                'skills'=>$request->input('skills'),
                'company_name'=>$request->input('company_name'),
                'company_website'=>$request->input('company_website'),
                'user_id'=>$user->id,
                // 'user_id'=>Auth()::user()->id,

                ]);
                return response()->json([
                    'status'=>'ok',
                    'message'=>'client profile updated successfully',
                    'data'=>$updatedData,
                ],200);
            }
    }
}

