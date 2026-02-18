<?php

namespace App\Enums;

enum LogAction: string
{
    case Create = 'CREATE';
    case Update = 'UPDATE';
    case Delete = 'DELETE';
    case Login = 'LOGIN';
    case Logout = 'LOGOUT';
    case Submit = 'SUBMIT';
    case Review = 'REVIEW';
    case Verify = 'VERIFY';
    case Reject = 'REJECT';
    case RequestRevision = 'REQUEST_REVISION';

    public function label(): string
    {
        return match ($this) {
            self::Create => __('common.log-action.create'),
            self::Update => __('common.log-action.update'),
            self::Delete => __('common.log-action.delete'),
            self::Login => __('common.log-action.login'),
            self::Logout => __('common.log-action.logout'),
            self::Submit => __('common.log-action.submit'),
            self::Review => __('common.log-action.review'),
            self::Verify => __('common.log-action.verify'),
            self::Reject => __('common.log-action.reject'),
            self::RequestRevision => __('common.log-action.request-revision'),
        };
    }
}
