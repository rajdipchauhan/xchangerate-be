<?php

namespace App\Http\Controllers;

use App\Auth\Auth;
use App\Views\SettingView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingController extends ApiController
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
        $view = new SettingView();

        return response()->json($view->render($this->auth->user()));
    }

    public function update(Request $request)
    {
        $user = $this->auth->user();
        $validator = Validator::make($request->input(), [
            'entry_frugality_ratio' => 'required',
            'entry_price_relativity_ratio' => 'required',
            'entry_notified_by_email' => 'required',
            'entry_notified_by_sms' => 'required',
            'entry_is_auto_trading' => 'required',
            'exit_target' => 'required',
            'exit_shrink_differential' => 'required',
            'exit_option' => 'required',
            'exit_notified_by_email' => 'required',
            'exit_notified_by_sms' => 'required',
            'exit_is_auto_trading' => 'required',
            'withdrawal_capital_balance_currency' => 'required',
            'withdrawal_value' => 'required',
            'withdrawal_value_coin' => 'required',
            'withdrawal_address' => 'required',
            'withdrawal_notified_by_email' => 'required',
            'withdrawal_notified_by_sms' => 'required',
            'withdrawal_is_auto_trading' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user->update($request->only([
            'entry_frugality_ratio',
            'entry_price_relativity_ratio',
            'entry_notified_by_email',
            'entry_notified_by_sms',
            'entry_is_auto_trading',
            'exit_target',
            'exit_shrink_differential',
            'exit_option',
            'exit_notified_by_email',
            'exit_notified_by_sms',
            'exit_is_auto_trading',
            'withdrawal_capital_balance',
            'withdrawal_capital_balance_currency',
            'withdrawal_value',
            'withdrawal_value_coin',
            'withdrawal_address',
            'withdrawal_notified_by_email',
            'withdrawal_notified_by_sms',
            'withdrawal_is_auto_trading',
        ]));

        $view = new SettingView();

        return response()->json($view->render($user));
    }
}