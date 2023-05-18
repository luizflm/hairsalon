<x-layout
page="Hairsalon - Login"
btnHref="{{route('register')}}"
btnText="Registrar-se"
increaseBanner="true"
>
    <x-slot:bannerContent>
        <div class="container-fluid form py-5">
            <div class="container d-flex flex-column align-items-center p-5">
                <x-form.form_title title="Login" />
                <form method="POST" action={{route('login_action')}} class="mt-2">
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
                    value="{{old('email')}}"
                    />
                    <x-form.input
                    type="password"
                    name="password"
                    placeholder="Digite sua senha"
                    required="true"
                    label="Senha"
                    />
                    <x-form.submit_btn btnText="Fazer Login" />
                    
                    <div class="mb-3 d-flex justify-content-center">
                        <a href={{route('register')}} class="btn">Ainda não tem uma conta? Registre-se</a>
                    </div>
                </form>
            </div>
        </div>
    </x-slot:bannerContent>
</x-layout>