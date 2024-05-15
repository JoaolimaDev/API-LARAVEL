<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Api\Auth\TokenAbility;

class CheckForAnyAbility
{
    /**
     * Handle an incoming request.
     *
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $token = $request->header('Authorization');
        $currentDate = Carbon::now();

        if (!$token) {
            return response()->json(['Status' => 401 ,'Mensagem' =>['Adicione o bearer token!']], 401);
        }

        $isToken = DB::select('SELECT * FROM personal_access_tokens WHERE token = ?', [trim(trim($token, "Bearer"))]);

        if (empty($isToken)) {
            return response()->json(['Status' => 403, 'Mensagem' =>  ["Token inválido!"]], 403);
        }

        if (trim($isToken[0]->abilities, '[ " "]') !== TokenAbility::ACCESS_API->value) {
            return response()->json(['Status' => 403, 'Mensagem' =>  ["Este token não possui as permissões necessárias!"]], 403);
        }

        if (Carbon::parse($isToken[0]->expires_at)->lt($currentDate)) {
            return response()->json(['Status' => 401, 'Mensagem' =>  ["Token expirado!"]], 401);
        }

        $user = User::where('id', $isToken[0]->tokenable_id)->first();

        $request->merge(['user' => $user]);

        return $next($request);
    }
}
