<?php

namespace App\Http\Controllers\Api\Auth;
class Validation
{

    public function val($var) {

        $message = array_map(function ($arr){

            if ($arr == 'The password field must be at least 8 characters.') {
                return "O campo senha deve ter pelo menos 8 caracteres.";
            }elseif ($arr == 'The password field must contain at least one uppercase and one lowercase letter.') {
                return "O campo senha deve conter pelo menos uma letra maiúscula e uma letra minúscula.";
            }elseif ($arr == 'The email has already been taken.') {
                return "Email já cadastrado!";
            }

           return "Preencha todos os campos!";
        }, array_values($var)[0]);

        return $message;

    }

}

?>
