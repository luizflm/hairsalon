<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function getInfo($id) {
        $array = ['error' => ''];

        $user = User::find($id);
        if($user) {
            $array['data'] = $user;
        } else {
            $array['error'] = 'Usuário não encontrado.';
            return $array;
        }

        return $array;
    }

    public function updateView($id) {
        $loggedUser = Auth::user();
        $user = User::find($id);
        if($user && $loggedUser->id == $user->id) {
            return view('edit_user', ['formTitle' => 'Configurações', 'user' => $user]);
        } else {
            return redirect()->route('home');
        }
    }

    public function updateAction($id, Request $request) {
        $loggedUser = Auth::user();
        $user = User::find($id);

        $validator = $request->validate([
            'email' => ['required', 'email', Rule::unique('users')->ignore($loggedUser->id)],
            'name' => ['required', 'min:2'],
            'cpf' => ['required', 'digits:11', Rule::unique('users')->ignore($loggedUser->id)],
            'password' => ['required', 'min:4'],
            'password_confirm' => ['required', 'same:password'],
        ]);
        if($validator) {    
            if($user && $loggedUser->id == $user->id) {
                $name = $request->name;
                $email = $request->email;
                $password = $request->password;
                $cpf = $request->cpf;
                $hash = Hash::make($password);

                $name = trim($name," ");
                $name = explode(' ', $name);
                if(count($name) > 1) {
                    $formatedName = $name[0].' '.last($name);
                } else {
                    $formatedName = $name[0];
                }

                $user->update([
                    'name' => $formatedName,
                    'email' => $email,
                    'password' => $hash,
                    'cpf' => $cpf,
                ]);
            }
        }

        return redirect()->route('edit_user', ['id' => $loggedUser->id]);
    }

    public function delete($id) {
        $array = ['error' => ''];

        $user = User::find($id);
        if($user) {
            $user->delete();
        } else {
            $array['error'] = 'Usuário não encontrado.';
            return $array;
        }

        return $array;
    }
}
