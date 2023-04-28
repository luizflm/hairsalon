<x-admin_layout
page="Hairsalon - Adicionar Cabelereiro(a)"
increaseBanner="true"
>
<div class="container-fluid form p-0 pt-4 py-sm-5 py-lg-5">
    <div class="container d-flex flex-column align-items-center pb-sm-3 px-sm-3 py-sm-2">
        <x-form.form_title title="Adicionar Funcionário" />
        <form method="POST" enctype="multipart/form-data" action={{route('insert_hairdresser_action')}} class="mt-2">
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
            placeholder="Digite o nome"
            required="true"
            label="Nome"
            value="{{old('name')}}"
            />

            <div class="mb-3">
                <label for="specialties" class="form-label">Especialidades</label>
                <textarea id="specialties" name="specialties" class="form-control" required>{{old('specialties')}}</textarea>
            </div>

            <label class="form-label">Dias</label>

            <div class="btn-group d-flex justify-content-center mb-3" role="group" aria-label="Basic checkbox toggle button group">
                    <x-form.checkbox
                    name="days[]"
                    id="day0"
                    value="0"
                    label="Domingo"
                    />
            </div>

            <div class="btn-group d-flex justify-content-center mb-3" role="group" aria-label="Basic checkbox toggle button group">
                @for($i=1;$i<4;$i++)
                    <x-form.checkbox
                    name="days[]"
                    id="day{{$i}}"
                    value="{{$i}}"
                    label="{{$days[$i]}}"
                    />
                @endfor
            </div>

            <div class="btn-group d-flex justify-content-center mb-3" role="group" aria-label="Basic checkbox toggle button group">
                @for($i=4;$i<7;$i++)
                    <x-form.checkbox
                    name="days[]"
                    id="day{{$i}}"
                    value="{{$i}}"
                    label="{{$days[$i]}}"
                    />
                @endfor
            </div>
            
            <div class="row">
                <x-form.select
                col="true"
                label="Horário Inicial"
                name="start_time"
                required="true"
                >
                    <option disabled selected>ex: 09:00</option>
                    @foreach($times as $time)
                        <option value="{{$time}}">{{$time}}</option>
                    @endforeach
                </x-form.select>
                <x-form.select
                col="true"
                label="Horário Final"
                name="end_time"
                required="true"
                >
                    <option disabled selected>ex: 16:00</option>
                    @foreach($times as $time)
                        <option value="{{$time}}">{{$time}}</option>
                    @endforeach
                </x-form.select>
            </div>
            
            <x-form.input
            type="file"
            name="avatar"
            placeholder="Digite o nome"
            label="Foto"
            value="{{old('avatar')}}"
            />
            <x-form.submit_btn btnText="Adicionar" />

        </form>
    </div>
</div>


</x-admin_layout>