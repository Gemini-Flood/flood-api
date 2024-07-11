<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\HelperController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends HelperController
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->globalResponse(true, 400, $validator->getMessageBag(), 'Formulaire invalide');
        }

        $user = User::whereEmail($request->email)->first();

        if($user){
            return $this->globalResponse(false, 200, $user, 'Utilisateur connecté');
        }else{
            return $this->globalResponse(true, 400, $user, "Utilisateur introuvable");
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->globalResponse(true, 400, $validator->getMessageBag(), 'Formulaire invalide');
        }

        $user = User::where('email', $request->email)->first();
        if ($user) {
            return $this->globalResponse(true, 401, $user, "L'adresse email est déjà utilisée");
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email
        ]);

        if($user){
            return $this->globalResponse(false, 200, $user, 'Utilisateur ajouté');
        }else{
            return $this->globalResponse(true, 400, $user, "Erreur lors de l'ajout de l'utilisateur");
        }

    }
}
