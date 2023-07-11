<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Client;
use App\Models\Vendor;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Events\SendVerificationEmail;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Config;
use Twilio\Exceptions\TwilioException;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Validation\ValidationException;
use Twilio\Rest\Api\V2010\Account\MessageList;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $validatedData = $request->validated();

        $profileImagePath = null;
        if ($request->hasFile('profile_image')) {
            $profileImage = $request->file('profile_image');
            $profileImageName = Str::slug($profileImage->getClientOriginalName());
            $profileImageExtension = $profileImage->getClientOriginalExtension();
            $profileImageName = $profileImageName . '_' . time() . '.' . $profileImageExtension;
            $profileImagePath = $profileImage->storeAs('profile_images', $profileImageName, 'public');
        }


        $user = User::create([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'email' => $validatedData['email'],
            'mobile_number' => $validatedData['mobile_number'],
            'password' => Hash::make($validatedData['password']),
            'profile_image' => $profileImagePath,
            'country' => $validatedData['country'],

        ]);

        $role = Role::where('name', $request->input('role'))->firstOrFail();
        $user->assignRole($role);

        $token = $user->createToken('auth_token')->plainTextToken;
        $user->sendEmailVerificationNotification();
        event(new SendVerificationEmail($user));
        return response()->json([
            'message' => 'User created successfully please verify your email',
            'token' => $token,
            'user' => new UserResource($user),
        ], 200);
    }

    public function login(LoginRequest $request)
    {

        $validatedData = $request->validated();

        if (!Auth::attempt($validatedData)) {
            return response()->json(['message' => 'Invalid login credentials'], 401);
        }

        $user = User::where('email', $validatedData['email'])->first();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
        ], 200);
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (Exception $e) {
            return response()->json(['message' => 'Google authentication failed'], 401);
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            $nameParts = explode(' ', $googleUser->getName());
            $firstName = $nameParts[0] ?? '';
            $lastName = isset($nameParts[1]) ? implode(' ', array_slice($nameParts, 1)) : '';
            $user = User::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $googleUser->getEmail(),
                'password' => Hash::make(Str::random(16)),
                'email_verified_at' => now(),
                'profile_image' => $googleUser->getAvatar(),
                'gauth_id' => $googleUser->getId(),
                'gauth_type' => 'google',
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    public function enableTwoFactor(Request $request)
    {
        $user = Auth::user();

        if ($user->two_factor === 1) {
            return response()->json(['message' => 'Two-factor authentication is already enabled'], 400);
        }

        if (!$user->mobile_number) {
            return response()->json(['message' => 'Mobile number is required to enable two-factor authentication'], 400);
        }

        $user->two_factor = 1;
        $user->save();
        $this->sendTwoFactorCode();

        return response()->json(['message' => 'Two-factor authentication enabled'], 200);
    }

    public function disableTwoFactor(Request $request)
    {
        $user = Auth::user();

        if ($user->two_factor === 0) {
            return response()->json(['message' => 'Two-factor authentication is already disabled'], 400);
        }

        $user->two_factor = 0;
        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->save();

        return response()->json(['message' => 'Two-factor authentication disabled'], 200);
    }

    protected function sendTwoFactorCode()
    {
        $user = Auth::user();

        if ($user->two_factor === 0) {
            return;
        }

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $expiresAt = now()->addMinutes(10);
        $user->two_factor_code = $code;
        $user->two_factor_expires_at = $expiresAt;
        $user->save();

        $formattedPhoneNumber = '+92' . $user->mobile_number;

        try {
            $sid = env("TWILIO_SID");
            $token = env("TWILIO_AUTH_TOKEN");
            $twilioNumber = env("TWILIO_PHONE_NUMBER");

            $client = new Client($sid, $token);
            $client->messages->create(
                $formattedPhoneNumber,
                [
                    'from' => $twilioNumber,
                    'body' => 'Your two-factor authentication code: ' . $code,
                ]
            );
        } catch (TwilioException $e) {
            return response()->json(['message' => 'Failed to send the two-factor authentication code'], 500);
        }
    }

    public function verifyTwoFactor(Request $request)
    {
        $user = Auth::user();

        if ($user->two_factor === 0) {
            return response()->json(['message' => 'Two-factor authentication is not enabled'], 400);
        }

        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        if ($request->code !== $user->two_factor_code) {
            return response()->json(['message' => 'Invalid two-factor authentication code'], 401);
        }

        $expiresAt = Carbon::parse($user->two_factor_expires_at);

        if ($expiresAt->isPast()) {
            return response()->json(['message' => 'Two-factor authentication code has expired'], 401);
        }

        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->save();
        return response()->json(['message' => 'Two-factor authentication verified successfully'], 200);
    }


    //forget password code
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
        ]);
        $token = Str::random(64);
        $userInfo = DB::table('users')->where('email', $request->email)->select('email')->first();
        if (!empty($userInfo)) {
            DB::table('password_reset_tokens')->insert([
                'email'         => $request->email,
                'token'         =>  $token,
                'created_at'    => Carbon::now()
            ]);

            Mail::send('forgetPassword', ['token' => $token], function ($message) use ($request) {
                $message->to($request->email);
                $message->subject('Reset Password');
            });

            return response()->json([
                'status' => true,
                'message' => 'Password link sent in your email',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Sorry that email not found in our record',
            ], 401);
        }
    }
    public function getemail_fromtoken(Request $request, $id)
    {
        $data = DB::table('password_reset_tokens')->where('token', $id)->first();
        return response()->json([
            'status' => true,
            'data' => $data->email,
        ], 200);
    }
    public function submitResetPasswordForm(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
            'password' => 'required|string|min:6',
            'confirmpassword' => 'required|min:6|same:password',
        ]);
        $updatePassword = DB::table('password_reset_tokens')
            ->where([
                'email' => $request->email,
                // 'token' => $request->token,
            ])
            ->first();
        if (!$updatePassword) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid token!',
            ], 401);
        }
        $user = User::where('email', $request->email)
            ->update(['password' => Hash::make($request->password)]);
        DB::table('password_reset_tokens')->where(['email' => $request->email])->delete();
        return response()->json([
            'status' => true,
            'message' => 'Your password is updated kindly login again!'
        ], 200);
    }
}
