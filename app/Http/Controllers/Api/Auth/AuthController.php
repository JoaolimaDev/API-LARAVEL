<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
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

            if (!$user) {
                return response()->json(["Status" => 400, "Mensagem" => ["Email ou senha inválidos!"]], 400);
            }

            $hash = Hash::check($request->password, $user->password);

            if (!$hash) {
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
                    "expires_at" => $accessToken->expires_at,
                    "created_at" => $accessToken->created_at,
                    "abilities" => $accessToken->abilities
                ],
                'RefreshToken' => [
                    "token" => $refreshToken->token,
                    "expires_at" => $refreshToken->expires_at,
                    "created_at" => $refreshToken->created_at,
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

            if (!$user) {
                return response()->json(["Status" => 400, "Mensagem" => ["Email ou senha inválidos!"]], 400);
            }

            $hash = Hash::check($request->password, $user->password);

            if (!$hash) {
                return response()->json(["Status" => 400, "Mensagem" => ["Email ou senha inválidos!"]], 400);
            }

            $pwordToken = Str::random(60);

            DB::delete('DELETE FROM password_reset_tokens WHERE email = ?', [$request->email]);

            DB::insert('INSERT INTO password_reset_tokens (email, token, created_at, expires_at) VALUES (?, ?, ?, ?)',
            [$request->email, $pwordToken, Carbon::now(), Carbon::now()->addHour()]);

            return response()->json([
                "Status"=> 200,
                "Mensagem" => ["Usuário {$user->name}, reset token gerado!"],
                'Reeset Token' => $pwordToken,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 500,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function CreateUser(Request $request) {

        try {

            $validateUser = Validator::make($request->all(),
            [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => ['required', Password::min(8)
                ->mixedCase()]
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

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            $accessToken = $user->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')))->accessToken;
            $refreshToken = $user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], Carbon::now()->addMinutes(config('sanctum.rt_expiration')))->accessToken;

            return response()->json([
                "Status"=> 201,
                "Mensagem" => ["Usuário {$user->name} criado!"],
                'AccessToken' => [
                    "token" => $accessToken->token,
                    "expires_at" => $accessToken->expires_at,
                    "created_at" => $accessToken->created_at,
                    "abilities" => $accessToken->abilities
                ],
                'RefreshToken' => [
                    "token" => $refreshToken->token,
                    "expires_at" => $refreshToken->expires_at,
                    "created_at" => $refreshToken->created_at,
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

}
