<x-admin_layout
page="Hairsalon - Editar Cabelereiro(a)"
increaseBanner="true"
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

            <label class="form-label">Dias</label>
            <div class="btn-group d-flex justify-content-center mb-3" role="group" aria-label="Basic checkbox toggle button group">
                @for($i=0;$i<3;$i++) {{--$i = 0 --}}
                    @php
                        $dayKey =  array_search($days[$i], $days); // 1
                        $isWorkDay = in_array($dayKey, $workDays); // 1 ["1", "2", "3", "4", "5"]
                    @endphp

                    @if($isWorkDay)
                        <x-form.checkbox
                        checked="true"
                        name="days[]"
                        id="day{{$i}}"
                        value="{{$i}}"
                        label="{{$days[$i]}}"
                        />
                    @else
                        <x-form.checkbox
                        name="days[]"
                        id="day{{$i}}"
                        value="{{$i}}"
                        label="{{$days[$i]}}"
                        />
                    @endif
                @endfor
            </div>

            <div class="btn-group d-flex justify-content-center mb-3" role="group" aria-label="Basic checkbox toggle button group">
                @for($i=3;$i<7;$i++)
                    @php
                        $dayKey =  array_search($days[$i], $days);
                        $isWorkDay = in_array($dayKey, $workDays);
                    @endphp

                    @if($isWorkDay)
                    <x-form.checkbox
                        checked="true"
                        name="days[]"
                        id="day{{$i}}"
                        value="{{$i}}"
                        label="{{$days[$i]}}"
                        />
                    @else
                        <x-form.checkbox
                        name="days[]"
                        id="day{{$i}}"
                        value="{{$i}}"
                        label="{{$days[$i]}}"
                        />
                    @endif
                @endfor
            </div>

            <div class="row">
                <x-form.select
                col="true"
                label="Horário Inicial"
                name="start_time"
                required="true"
                >
                    @foreach($times as $time)
                        @if($workHours[0] == $time)
                            <option value="{{$time}}" selected>{{$time}}</option>
                        @else
                            <option value="{{$time}}">{{$time}}</option>
                        @endif
                    @endforeach
                </x-form.select>

                <x-form.select
                col="true"
                label="Horário Final"
                name="end_time"
                required="true"
                >
                    @foreach($times as $time)
                        @if(last($workHours) == $time)
                            <option value="{{$time}}" selected>{{$time}}</option>
                        @else
                            <option value="{{$time}}">{{$time}}</option>
                        @endif
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
            <x-form.submit_btn btnText="Aplicar alterações" />

        </form>
    </div>
</div>


</x-admin_layout>