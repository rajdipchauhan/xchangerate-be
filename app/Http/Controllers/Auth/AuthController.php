<?php

namespace App\Http\Controllers\Auth;

use App\Auth\Auth;
use App\Helpers\EmailHelper;
use App\Http\Controllers\ApiController;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends ApiController
{
    /**
     * @var Auth
     */
    protected $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function postRegistration(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
            'country' => 'required|max:255',
            'city' => 'required|max:255',
            'phone' => 'required|numeric',
            'currency' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->country = $request->country;
            $user->city = $request->city;
            $user->phone = $request->phone;
            $user->currency = $request->currency;
            $user->verification_code = md5(time() . rand(1, 99999));
            $user->save();
            $user->setVisible(['name', 'email', 'phone']);

            EmailHelper::SendWelcomeEmail($user->email, $user->verification_code);

            return response()->json(['user' => $user]);
        } catch (\Exception $ex) {
            Log::error($ex);

            return response()->json($ex->getMessage(), 500);
        }
    }

    public function verify(Request $request, $verificationCode)
    {
        $user = User::whereVerified(false)->whereVerificationCode($verificationCode)->first();

        if ($user) {
            $user->verified = true;
            $user->save();

            return response()->json();
        }

        return response()->json('Verification failed', 500);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'email' => 'required',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        /** @var User $user */
        $user = User::whereEmail($request->email)->first();
        if ($user && ! $user->verified) {
            return response()->json('Account not verified', 401);
        }

        if ($user && Hash::check($request->password, $user->password)) {
            $user->last_login_ip = $request->ip();
            $user->last_login_date = Carbon::now();
            $user->save();
            $user->setVisible([
                'name',
                'email',
                'phone',
                'currency',
                'country',
                'city'
            ]);

            $jwt = $this->auth->generateJwt($user, (bool)($request->input('remember_me')));

            return response()->json([
                'user' => $user,
                'api_key' => $jwt
            ], 201);
        }

        return response()->json('Invalid credentials', 401);
    }

    public function logout()
    {
        $this->auth->invalidateJwt();

        return response()->json();
    }
}