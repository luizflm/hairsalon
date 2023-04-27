<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function insertView() {
        return view('register');
    }

    public function insertAction(Request $request) {
        $cpf_regex = '/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/'; // regex de cpf

        $validator = $request->validate([
            'email' => 'required|email|unique:users,email',
            'name' => 'required|min:2',
            'cpf' => ['required', 'regex:'.$cpf_regex],
            'password' => 'required|min:4',
            'password_confirm' => 'required|same:password'
        ]);
        if($validator) {
            // recebendo os dados do form request
            $name = $request->name;
            $cpf = $request->cpf;
            $cpf = str_replace(['.', '-'], '', $cpf); // deixando o cpf apenas com os números, pra salvar no banco
            $email = $request->email;
            $password = $request->password;
            $hash = Hash::make($password); // criando hash da senha

            // verificando se o cpf existe, não dá pra colocar "unique:users,cpf" no validator
            // pq no form request o cpf está vindo com "." e "-", e no banco de dados só ficam salvos os números
            $cpfExists = User::where('cpf', $cpf)->first();
            if($cpfExists) { 
                // caso exista, volta pra view com os inputs preenchidos e com o erro
                return redirect()->back()->withErrors([
                    'email' => 'CPF já está em uso.'
                ])->withInput($request->except(['password', 'password_confirm']));
            }

            $name = trim($name," "); // tirando espaços desnecessários da string
            $name = explode(' ', $name); // transformando a string de nome em array
            // verificando se tem mais de um item no array (nome e sobrenome ou nome composto)
            if(count($name) > 1) {
                // se sim, forma a string com o primeiro e o ultimo nome enviados.
                $formatedName = $name[0].' '.last($name);
            } else { // senão, forma a string normalmente, apenas com o nome único enviado.
                $formatedName = $name[0];
            }

            // verificando se o nome é "admin"
            if($formatedName === "admin") {
                // se sim, verificar se já existe o usuário "admin" no banco de dados
                $admin = User::where('name', 'admin')->first();
                if($admin) { 
                    // se existir, voltar para a tela e mostrar erro na view
                    return redirect()->back()->withErrors([
                        'email' => 'Nome inválido, tente colocar um diferente.',
                    ])->withInput($request->except(['password', 'password_confirm']));
                } else {
                    // se não existir, criar o usuário "admin" que terá acesso à rotas de admin
                    $newUser = User::create([
                        'name' => $formatedName,
                        'cpf' => $cpf,
                        'email' => $email,
                        'password' => $hash,
                        'is_admin' => '1',
                    ]);
                }
            } else { // se o nome não for "admin", cria o usuário sem mais restrições
                $newUser = User::create([
                    'name' => $formatedName,
                    'cpf' => $cpf,
                    'email' => $email,
                    'password' => $hash,
                ]);
            }
            // após criar o usuário, respectivamente ele é logado no sistema.
            Auth::login($newUser);

            // após ser logado, é redirecionado para a home
            return redirect()->route('home');
        } else { // caso tenha dado erro no validator, volta pra view de register com os valores anteriores
            return redirect()->back()->onlyInput(['email', 'name', 'cpf']);
        }
    }

    public function loginView() {
        return view('login');
    }

    public function loginAction(Request $request) {
        $credentials = $request->validate([ // o login é feito com email e password
            'email' => 'required|email',
            'password' => 'required',
        ]);
        // ao tentar logar com as informações dadas, irá retornar true se logou, e false se não
        if(Auth::attempt($credentials)) {  // se true:
            $request->session()->regenerate(); // gera outro token de sessão

            return redirect()->route('home'); // redireciona para a home
        }
        // se der erro, volta pra view com o erro e com o input de email preenchido
        return redirect()->back()->withErrors([
            'email' => 'O email e/ou senha estão incorretos.',
        ])->onlyInput('email');
    }

    public function logout(Request $request) {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
