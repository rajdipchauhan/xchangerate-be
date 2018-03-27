<?php

namespace App\Http\Controllers;

use App\Auth\Auth;
use App\Views\UserView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends ApiController
{
    /**
     * @var Auth
     */
    private $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function show()
    {
        return response()->json($this->auth->user());
    }

    public function update(Request $request)
    {
        $user = $this->auth->user();
        $validator = Validator::make($request->input(), [
            'name' => 'required|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'password' => 'min:6|confirmed',
            'country' => 'required|max:255',
            'city' => 'required|max:255',
            'phone' => 'required|numeric',
            'currency' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user->fill($request->only([
            'name',
            'email',
            'country',
            'city',
            'phone',
            'currency'
        ]));
        if ($password = $request->get('password')) {
            $user->password = Hash::make($password);
        }
        $user->save();
        $view = new UserView();

        return response()->json($view->render($user));
    }
}