<?php

namespace App\Http\Controllers\services;

use App\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\Auth\Validation;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function All() {

        $categories = Category::all();
        return response()->json(['Status' => 200, 'Mensagem' =>  [$categories]], 200);
    }

    public function Update(Request $request, int $id) {

        $validateCategory = Validator::make($request->all(),
        [
            'name' => 'string|max:255',
            'description' => 'string'
        ]);

        $category = Category::find($id);

        if (!$category) {
            return response()->json(['Status' => 400, 'Mensagem' =>  ["Categoria não encontrada, ID inválido!"]], 400);
        }

        $category->update($validateCategory->validated());

        return response()->json(['Status' => 200, 'Mensagem' =>
        ["A categoria {$category->name} foi atualizada com sucesso!"]], 200);
    }

    public function FindById(int $id) {

        $category = Category::find($id);

        if (!$category) {
            return response()->json(['Status' => 400, 'Mensagem' =>  ["Categoria não encontrada, ID inválido!"]], 400);
        }

        return response()->json(['Status' => 200, 'Mensagem' =>  [$category]], 200);
    }

    public function Delete(int $id) {

        $category = Category::find($id);

        if (!$category) {
            return response()->json(['Status' => 400, 'Mensagem' =>  ["Categoria não encontrada, ID inválido!"]], 400);
        }

        $category->delete();
        return response()->json(['Status' => 200,
        'Mensagem' =>  ["A Categoria {$category->name} foi excluida com sucesso!"]], 200);
    }

    public function Register(Request $request) {

        $validateCategory = Validator::make($request->all(),
        [
            'name' => 'required|string|max:255',
            'description' => 'required|string'
        ]);

        $errors = $validateCategory->errors()->getMessages();

        $message = new Validation();

        if($validateCategory->fails()){
            return response()->json([
                'Status' => 401,
                'Mensagem' => $message->val($errors),
                'Campos' => array_keys($errors)
            ], 401);
        }

        $category = Category::create($validateCategory->validated());

        return response()->json([
            'Status' => 200,
            'Mensagem' => ["A {$category->name} foi cadastrada com sucesso!"]
        ], 200);

    }
}
