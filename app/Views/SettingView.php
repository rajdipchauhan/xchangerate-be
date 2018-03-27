<?php

namespace App\Views;

class SettingView extends AbstractView
{
    protected $fields = [
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
    ];
}