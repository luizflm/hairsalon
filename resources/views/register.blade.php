<x-layout
page="Hairsalon - Registro"
btnHref="{{route('login')}}"
btnText="Fazer Login"
increaseBanner="true"
>
<x-slot:bannerContent>
    <div class="container-fluid form py-3 py-lg-5">
        <div class="container d-flex flex-column align-items-center p-3">
            <x-form.form_title title="Registre-se" />
            <form method="POST" action={{route('register_action')}} class="mt-2">
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
                type="text"
                name="name"
                placeholder="Digite seu nome"
                required="true"
                label="Nome"
                value="{{old('name')}}"
                />
                <x-form.input
                type="text"
                name="cpf"
                placeholder="Digite seu CPF"
                required="true"
                label="CPF"
                value="{{old('cpf')}}"
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
                placeholder="Digite sua senha novamente"
                required="true"
                label="Confirme sua senha"
                />
                <x-form.submit_btn btnText="Fazer registro" />
                
                <div class="mb-3 d-flex justify-content-center">
                    <a href="{{route('login')}}" class="btn">Já tem uma conta? Faça login</a>
                </div>
            </form>
        </div>
    </div>
</x-slot:bannerContent>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $("#cpf").mask("000.000.000-00");
    });
</script>
</x-layout>