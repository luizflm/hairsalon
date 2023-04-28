<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function updateView($id) {
        $loggedUser = Auth::user(); // pegando o usuário logado
        $user = User::find($id); // pegando o usuário que será editado
        // verificando se foi encontrado um usuário e se o usuário a ser editado é o usuário logado
        if($user && $loggedUser->id == $user->id) { // se sim
            // renderiza a view, enviando os dados do usuário a ser editado
            return view('edit_user', ['user' => $user]);
        } else { // senão
            // retorna para a home
            return redirect()->route('home');
        }
    }

    public function updateAction($id, Request $request) {
        $loggedUser = Auth::user(); // pegando o usuário logado
        $user = User::find($id); // pegando o usuário que será editado
        // removendo os "-" e "." da string de cpf
        $request['cpf'] = str_replace(['.', '-'], '', $request['cpf']); 
        // fazendo o form request validation
        $validator = $request->validate([
            'email' => ['required', 'email', Rule::unique('users')->ignore($loggedUser->id)],
            'name' => ['required', 'min:2'],
            'cpf' => ['required', 'digits:11', Rule::unique('users')->ignore($loggedUser->id)],
            'password' => ['required', 'min:4'],
            'password_confirm' => ['required', 'same:password'],
        ]);
        if($validator) { // se não deu erro no validator
            // verifica se o usuário a ser editado foi encontrado e se é o mesmo usuário que está logado
            if($user && $loggedUser->id == $user->id) { // se sim
                // recebe os dados
                $name = $request->name;
                $email = $request->email;
                $password = $request->password;
                $cpf = $request->cpf;
                $hash = Hash::make($password); // criando o hash de senha

               
                $name = trim($name," "); // tirando os espaços desnecessários da string
                 // separando os nomes enviado em um array
                $name = explode(' ', $name);
                // caso seja nome composto ou nome com sobrenome
                if(count($name) > 1) {
                    // formata a string com o primeiro e o ultimo nome enviado
                    $formatedName = $name[0].' '.last($name);
                } else { // caso seja nome único, apenas atribua o valor único à variavel
                    $formatedName = $name[0];
                }

                if($formatedName == "admin" && $loggedUser->name != 'admin') { // verificando se o nome enviado é "admin"
                    // se sim, voltar para a página com os erros e os inputs com os mesmo valores anteriores
                    return redirect()->back()->withErrors([
                        'email' => 'Nome inválido.',
                    ])->withInput($request->except(['password', 'password_confirm']));
                }
                // se tudo deu certo, faz o update
                $user->update([
                    'name' => $formatedName,
                    'email' => $email,
                    'password' => $hash,
                    'cpf' => $cpf,
                ]);
                // redireciona para a home
                return redirect()->route('home');
            }
        }
        // se algo der errado, volta para a tela de edição
        return redirect()->route('edit_user', ['id' => $loggedUser->id]);
    }

    public function delete($id) {
        $loggedUser = Auth::user(); // pegando o usuário logado
        $user = User::find($id); // pegando o usuário a ser deletado

        if($user['id'] == $loggedUser->id) { // se o usuário a ser deletado for o usuário logado
            // deleta o usuário
            $user->delete();
        }
        // se deletar ou não o usuário, redireciona para a home
        return redirect()->route('home');
    }
}
