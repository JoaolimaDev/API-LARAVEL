<?php

namespace App\Http\Controllers\services;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Http\Controllers\Api\Auth\Validation;

class ServicesController extends Controller
{
    function UpdatePassword(Request $request) {

        try {

            $validateUser = Validator::make($request->all(),
            [
                'token' => 'required',
                'password' => ['required', Password::min(8)
                ->mixedCase()],
                'checkpassword' => 'required'
            ]);

            $errors = $validateUser->errors()->getMessages();
            $currentDate = Carbon::now();

            $message = new Validation();
            $password = $request->password;

            $isToken = DB::select('SELECT email, expires_at FROM password_reset_tokens WHERE token = ?', [$request->token]);

            if (empty($isToken)) {
                return response()->json([
                    'Status' => 401,
                    'Mensagem' => ["Reset token inválido!"]
                ], 401);
            }

            if (Carbon::parse($isToken[0]->expires_at)->lt($currentDate)) {
                return response()->json(['Status' => 401, 'Mensagem' =>  ["Reset token expirado!"]], 401);
            }

            if($validateUser->fails()){
                return response()->json([
                    'Status' => 400,
                    'Mensagem' => $message->val($errors),
                    'Campos' => array_keys($errors)
                ], 400);
            }

            if ($password !== $request->checkpassword) {
                return response()->json([
                    'Status' => 400,
                    'Mensagem' => ["Senhas não correspodem!"]
                ], 400);
            }

            $user = User::find($request->user->id);

            if (!$user) {
                return response()->json([
                    'Status' => 400,
                    'Mensagem' => ["Usuário não encontrado! Por favor tente novamente ou contate o suporte!"]
                ], 400);
            }

            $user->password = Hash::make($request->password);
            $user->save();

            DB::delete('DELETE FROM personal_access_tokens WHERE tokenable_id = ?', [$request->user->id]);
            DB::delete('DELETE FROM password_reset_tokens WHERE email = ?', [$request->user->email]);

            return response()->json([
                'Status' => 200,
                'Mensagem' => ["Usuário {$request->user->name}, senha alterada com sucesso! Por favor realize o login novamente!"]
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 500,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
