<x-admin_layout
page="Hairsalon - Editar Cabelereiro(a)"
>
<div class="container-fluid form py-3 py-lg-5">
    <div class="container d-flex flex-column align-items-center p-3">
        <x-form.form_title title="Editar Funcionário" />
        <form method="POST" enctype="multipart/form-data" action={{route('edit_hairdresser_action', $hairdresser['id'])}} class="mt-2">
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
            placeholder="Digite o nome"
            required="true"
            label="Nome"
            value="{{$hairdresser['name']}}"
            />

            <div class="mb-3">
                <label for="specialties" class="form-label">Especialidades</label>
                <textarea id="specialties" name="specialties" class="form-control" required>{{$hairdresser['specialties']}}</textarea>
            </div>

            <x-form.input
            type="file"
            name="avatar"
            placeholder="Digite o nome"
            label="Foto"
            value="{{old('avatar')}}"
            />
            <x-form.submit_btn btnText="Aplicar alterações" />

        </form>
    </div>
</div>


</x-admin_layout>