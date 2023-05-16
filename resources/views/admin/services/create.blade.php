<x-admin_layout
page="Hairsalon - Adicionar Serviço"
>
<div class="container-fluid form p-0 pt-4 py-sm-5 py-lg-5">
    <div class="container d-flex flex-column align-items-center pb-sm-3 px-sm-3 py-sm-2">
        <x-form.form_title title="Adicionar Serviço" />
        <form method="POST" action={{route('services.store')}} class="mt-2">
            @csrf

            @if($errors->any())
                <div class="alert">
                    <ul>
                        <li>{{ $errors->first() }}</li>
                    </ul>
                </div>
            @endif

            <x-form.input
            type="text"
            name="name"
            placeholder="Digite o serviço"
            required="true"
            label="Nome"
            value="{{old('name')}}"
            />

            <x-form.input
            type="number"
            name="price"
            placeholder="Digite o preço"
            required="true"
            label="Preço"
            step="any"
            value="{{old('price')}}"
            />

            <x-form.select
            label="Cabelereiro"
            name="hairdresser_id"
            required="true"
            >
                @foreach($hairdressers as $hairdresser)
                    <option value="{{$hairdresser['id']}}">{{$hairdresser['name']}}</option>
                @endforeach
            </x-form.select>
            
            <x-form.submit_btn btnText="Adicionar" />

        </form>
    </div>
</div>


</x-admin_layout>