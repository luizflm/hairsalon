<x-admin_layout
page="Hairsalon - Adicionar Cabelereiro(a)"
>
<div class="container-fluid form py-3 py-lg-5">
    <div class="container d-flex flex-column align-items-center p-3">
        <x-form.form_title title="Adicionar Serviço" />
        <form method="POST" action={{route('insert_service_action')}} class="mt-2">
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