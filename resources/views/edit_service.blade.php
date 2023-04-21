<x-admin_layout
page="Hairsalon - Editar Serviço"
>
<div class="container-fluid form py-3 py-lg-5">
    <div class="container d-flex flex-column align-items-center p-3">
        <x-form.form_title title="Editar Serviço" />
        <form method="POST" action={{route('edit_service_action', $service['id'])}} class="mt-2">
            @csrf
            @method('PUT')

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
            value="{{$service['name']}}"
            />

            <x-form.input
            type="number"
            name="price"
            placeholder="Digite o preço"
            required="true"
            label="Preço"
            step="any"
            value="{{$service['price']}}"
            />

            <x-form.select
            label="Cabelereiro"
            name="hairdresser_id"
            required="true"
            >
                @foreach($hairdressers as $hairdresser)
                    @if($hairdresser['id'] == $service['hairdresser_id'])
                        <option selected value="{{$hairdresser['id']}}">{{$hairdresser['name']}}</option>
                    @else
                        <option value="{{$hairdresser['id']}}">{{$hairdresser['name']}}</option>
                    @endif
                @endforeach
            </x-form.select>
            
            <x-form.submit_btn btnText="Aplicar" />

        </form>
    </div>
</div>


</x-admin_layout>