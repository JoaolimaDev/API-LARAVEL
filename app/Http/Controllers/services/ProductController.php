<?php

namespace App\Http\Controllers\services;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\Auth\Validation;
use App\Models\Category;

class ProductController extends Controller
{

    public function All() {

        $products = Product::all();
        return response()->json(['Status' => 200, 'Mensagem' =>  [$products]], 200);
    }

    public function FindById(int $id) {

        $products = Product::find($id);

        if (!$products) {
            return response()->json(['Status' => 400, 'Mensagem' =>  ["ID inválido!"]], 400);
        }

        return response()->json(['Status' => 200, 'Mensagem' =>  [$products]], 200);
    }

    public function Delete(int $id) {

        $product = Product::find($id);

        if (!$product) {
            return response()->json(['Status' => 400, 'Mensagem' =>  ["ID inválido!"]], 400);
        }

        $product->delete();

        return response()->json(['Status' => 200, 'Mensagem' =>
        ["O produto {$product->name} foi deletado com sucesso!"]], 200);
    }

    public function Register(Request $request) {

        $validateProduct = Validator::make($request->all(),
        [
            'name' => 'string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'category_id' => 'required|integer',
        ]);

        $errors = $validateProduct->errors()->getMessages();

        $message = new Validation();

        if($validateProduct->fails()){
            return response()->json([
                'Status' => 401,
                'Mensagem' => $message->val($errors),
                'Campos' => array_keys($errors)
            ], 401);
        }

        $category = Category::find($request->category_id);

        if (!$category) {
            return response()->json(['Status' => 401, 'Mensagem' =>  ["Categoria não encontrada!"]], 401);
        }

        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'category_id' => $request->category_id
        ]);

        return response()->json(["Status" => 201, "Mensagem" => ["O produto {$product->name} foi cadastrado!"],
        'Atributos' => [$product]], 201);
    }

    public function Update(Request $request,  $id) {

        $product = Product::find($id);

        if (!$product) {
            return response()->json(['Status' => 401, 'Mensagem' =>  ["ID não encontrada!"]], 401);
        }

        $validateProduct = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'description' => 'string',
            'price' => 'numeric',
            'stock' => 'integer',
            'category_id' => 'integer',
        ]);

        $product->update($validateProduct->validated());

        return response()->json(["Status" => 200, "Mensagem" => ["O produto {$product->name} foi atualizado!"],
        'Atributos' => [$product]], 200);
    }
}
