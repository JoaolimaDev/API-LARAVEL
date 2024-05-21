<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;


class AuthController extends Controller
{
    public function AuthLogin(Request $request) {

        try {

            $validateUser = Validator::make($request->all(),
            [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            $errors = $validateUser->errors()->getMessages();

            $message = new Validation();

            if($validateUser->fails()){
                return response()->json([
                    'Status' => 401,
                    'Mensagem' => $message->val($errors),
                    'Campos' => array_keys($errors)
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json(["Status" => 400, "Mensagem" => ["Email ou senha inválidos!"]], 400);
            }

            $user->tokens()->delete();

            $accessToken = $user->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')))->accessToken;
            $refreshToken = $user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], Carbon::now()->addMinutes(config('sanctum.rt_expiration')))->accessToken;

            return response()->json([
                "Status"=> 200,
                "Mensagem" => ["Usuário {$user->name} autenticado!"],
                'AccessToken' => [
                    "token" => $accessToken->token,
                    "expires_at" =>  Carbon::parse($accessToken->expires_at)->format('d-m-Y H:i:s'),
                    "created_at" =>  Carbon::parse($accessToken->created_at)->format('d-m-Y H:i:s'),
                    "abilities" => $accessToken->abilities
                ],
                'RefreshToken' => [
                    "token" => $refreshToken->token,
                    "expires_at" =>  Carbon::parse($refreshToken->expires_at)->format('d-m-Y H:i:s'),
                    "created_at" =>  Carbon::parse($refreshToken->created_at)->format('d-m-Y H:i:s'),
                    "abilities" => $refreshToken->abilities
                ],
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 500,
                'message' => $th->getMessage()
            ], 500);
        }


    }

    public function ResetPassword(Request $request) {

        try {

            $validateUser = Validator::make($request->all(),
            [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            $errors = $validateUser->errors()->getMessages();

            $message = new Validation();

            if($validateUser->fails()){
                return response()->json([
                    'Status' => 401,
                    'Mensagem' => $message->val($errors),
                    'Campos' => array_keys($errors)
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json(["Status" => 400, "Mensagem" => ["Email ou senha inválidos!"]], 400);
            }

            $pwordToken = Str::random(60);

            DB::delete('DELETE FROM password_reset_tokens WHERE email = ?', [$request->email]);

            DB::insert('INSERT INTO password_reset_tokens (email, token, created_at, expires_at) VALUES (?, ?, ?, ?)',
            [$request->email, $pwordToken, Carbon::now(), Carbon::now()->addHour()]);

            return response()->json([
                "Status"=> 200,
                "Mensagem" => ["Usuário {$user->name}, reset token gerado!"],
                'Reset Token' => $pwordToken,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 500,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function RefreshToken(Request $request) {

        $user = $request->user;

        $user->tokens()->where('tokenable_id', $request->user->id)->where('abilities', '["access-api"]')->delete();
        $accessToken = $user->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')))->accessToken;

        return response()->json([
            'Status' => 200,
            'Message' => "{$user->name}, novo access token criado!",
            'AccessToken' => [
                "token" => $accessToken->token,
                "expires_at" =>  Carbon::parse($accessToken->expires_at)->format('d-m-Y H:i:s'),
                "created_at" =>  Carbon::parse($accessToken->created_at)->format('d-m-Y H:i:s'),
                "abilities" => $accessToken->abilities
            ]
        ], 200);
    }

    public function Logout(Request $request){

        $request->user->tokens()->delete();

        return response()->json([
            "Status"=> 200,
            "Mensagem" => ["Usuário {$request->user->name}, deslogado!"]
        ], 200);
    }

    public function CreateUser(Request $request) {

        try {

            $validateUser = Validator::make($request->all(),
            [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => ['required', Password::min(8)
                ->mixedCase()],
                'role' => 'string'
            ]);

            $errors = $validateUser->errors()->getMessages();

            $message = new Validation();

            if($validateUser->fails()){
                return response()->json([
                    'Status' => 401,
                    'Mensagem' => $message->val($errors),
                    'Campos' => array_keys($errors)
                ], 401);
            }

            $user = User::create($validateUser->validated());

            $accessToken = $user->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')))->accessToken;
            $refreshToken = $user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], Carbon::now()->addMinutes(config('sanctum.rt_expiration')))->accessToken;

            return response()->json([
                "Status"=> 201,
                "Mensagem" => ["Usuário {$user->name} criado!"],
                'AccessToken' => [
                    "token" => $accessToken->token,
                    "expires_at" =>  Carbon::parse($accessToken->expires_at)->format('d-m-Y H:i:s'),
                    "created_at" =>  Carbon::parse($accessToken->created_at)->format('d-m-Y H:i:s'),
                    "abilities" => $accessToken->abilities
                ],
                'RefreshToken' => [
                    "token" => $refreshToken->token,
                    "expires_at" =>  Carbon::parse($refreshToken->expires_at)->format('d-m-Y H:i:s'),
                    "created_at" =>  Carbon::parse($refreshToken->created_at)->format('d-m-Y H:i:s'),
                    "abilities" => $refreshToken->abilities
                ],
            ], 201);

        } catch (\Throwable $th) {

            return response()->json([
                'status' => 500,
                'message' => $th->getMessage()
            ], 500);
        }

    }

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

            $user->tokens()->delete();

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
