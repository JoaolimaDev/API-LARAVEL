<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class checkUserAbilities
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('Authorization');
        $isToken = DB::select('SELECT * FROM personal_access_tokens WHERE token = ?', [str_replace("Bearer ", "",$token)]);

        $user = User::where('id', $isToken[0]->tokenable_id)->first();

        if ($user->role !== "admin") {
            return response()->json(['Status' => 401, 'Mensagem' =>
            ["Usuário {$user->name} não contém as permissões necessárias!"]], 401);
        }

        return $next($request);
    }
}
