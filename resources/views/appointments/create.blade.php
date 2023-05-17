<x-layout
page="Hairsalon - Agendamento"
increaseBanner="true"
>
<x-slot:bannerContent>
    <div class="container-fluid form p-0 pt-4 py-sm-5 py-lg-5">
        <div class="container d-flex flex-column align-items-center pb-sm-3 px-sm-3 py-sm-2">
            <x-form.form_title title="Agendamento" />
            <form method="POST" action={{route('appointments.store')}} class="mt-2">
                @csrf

                @if($errors->any())
                        <div class="alert">
                            <ul>
                                <li class="text-center">{{ $errors->first() }}</li>
                            </ul>
                        </div>
                    @endif

                <x-form.input
                type="date"
                name="ap_day"
                label="Dia desejado"
                required="true"
                />
                <x-form.select
                label="Horário"
                name="ap_time"
                required="true"
                >
                    <option disabled selected>ex: 09:00</option>
                    @foreach($times as $time)
                        @if($time != '12:00' && $time != last($times))
                            <option value="{{$time}}">{{$time}}</option>
                        @endif
                    @endforeach
                </x-form.select>
                <x-form.select
                label="Cabelereiro(a)"
                name="hairdresser_id"
                required="true"
                >
                    @foreach($hairdressers as $hairdresser)
                        <option value="{{$hairdresser['id']}}">{{$hairdresser['name']}}</option>
                    @endforeach
                </x-form.select>
                <x-form.select
                label="Serviço"
                name="service_id"
                required="true"
                >
                    @foreach($services as $service)
                        <option value="{{$service['id']}}">{{$service['name']}}</option>
                    @endforeach
                </x-form.select>

               <x-form.submit_btn btnText="Criar agendamento" />
            </form>
        </div>
    </div>
</x-slot:bannerContent>

<script>
    document.getElementById('hairdresser_id').addEventListener('change', function() {
    var hairdresserId = this.value;

    fetch('/hairdresser/services/' + hairdresserId)
        .then(function(response) {
        return response.json();
        })
        .then(function(data) {
            var serviceSelect = document.getElementById('service_id');

            // Limpa as opções existentes
            serviceSelect.innerHTML = '';

            // Adiciona as opções de serviço retornadas
            data.forEach(function(service) {
                var option = document.createElement('option');
                option.value = service.id;
                option.text = service.name + ' - R$ ' + service.price;
                serviceSelect.appendChild(option);
            });
        });
    });
</script>

</x-layout>