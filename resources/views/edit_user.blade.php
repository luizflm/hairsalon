<x-layout
page="Hairsalon - Configurações"
increaseBanner="true"
>
<x-slot:bannerContent>
    <div class="container-fluid form py-3 py-lg-5">
        <div class="container d-flex flex-column align-items-center py-2 px-4 p-lg-3">
            <x-form.form_title title="Configurações" />
            <form method="POST" action="{{route('edit_user_action', ['id' => $user['id']])}}" class="mt-2">
                @method('PUT')
                @csrf

                @if($errors->any())
                    <div class="alert">
                        <ul>
                            <li>{{ $errors->first() }}</li>
                        </ul>
                    </div>
                @endif

                <x-form.input
                type="email"
                name="email"
                placeholder="Digite seu email"
                required="true"
                label="Email"
                value="{{$user['email']}}"
                />
                <x-form.input
                type="text"
                name="name"
                placeholder="Digite seu nome"
                required="true"
                label="Nome"
                value="{{$user['name']}}"
                />
                <x-form.input
                type="text"
                name="cpf"
                placeholder="Digite seu CPF"
                required="true"
                label="CPF"
                value="{{$user['cpf']}}"
                />
                <x-form.input
                type="password"
                name="password"
                placeholder="Digite sua senha"
                required="true"
                label="Senha"
                />
                <x-form.input
                type="password"
                name="password_confirm"
                placeholder="Confirme sua senha"
                required="true"
                label="Confirme sua senha"
                />
                <x-form.submit_btn btnText="Aplicar alterações" />

                <a href="{{route('delete_user_action', ['id' => $user['id']])}}"
                class="btn d-flex justify-content-center btn-delete">Deletar conta</a>
                
            </form>
        </div>
    </div>
</x-slot:bannerContent>
</x-layout>