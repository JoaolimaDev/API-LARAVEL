<?php
namespace App\Http\Controllers\Api\Auth;

enum TokenAbility: string
{
    case ISSUE_ACCESS_TOKEN = 'issue-access-token';
    case ACCESS_API = 'access-api';
}

?>
