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
    public function updateView($id) {
        $loggedUser = Auth::user();
        $user = User::find($id);
        if($user && $loggedUser->id == $user->id) {
            return view('edit_user', ['user' => $user]);
        } else {
            return redirect()->route('home');
        }
    }

    public function updateAction($id, Request $request) {
        $loggedUser = Auth::user();
        $user = User::find($id);
        $cpf_regex = '/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/';

        $validator = $request->validate([
            'email' => ['required', 'email', Rule::unique('users')->ignore($loggedUser->id)],
            'name' => ['required', 'min:2'],
            'cpf' => ['required', 'regex:'.$cpf_regex, Rule::unique('users')->ignore($loggedUser->id)],
            'password' => ['required', 'min:4'],
            'password_confirm' => ['required', 'same:password'],
        ]);
        if($validator) {    
            if($user && $loggedUser->id == $user->id) {
                $name = $request->name;
                $email = $request->email;
                $password = $request->password;
                $cpf = $request->cpf;
                $cpf = str_replace(['.', '-'], '', $cpf);
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
        $loggedUser = Auth::user();
        $user = User::find($id);

        if($id == $loggedUser->id) {
            $user->delete();
        }
        
        return redirect()->route('home');
    }
}
