<x-layout
page="Hairsalon - Editar Agendamento"
increaseBanner="true"
>
<x-slot:bannerContent>
    <div class="container-fluid form p-0 pt-4 py-sm-5 py-lg-5">
        <div class="container d-flex flex-column align-items-center pb-sm-3 px-sm-3 py-sm-2">
            <x-form.form_title title="Editar" />
            <form method="POST" action={{route('appointments.update', $appointment['id'])}} class="mt-2">
                @csrf
                @method('PUT')

                @if($errors->any())
                        <div class="alert">
                            <ul>
                                <li class="text-center">{{ $errors->first() }}</li>
                            </ul>
                        </div>
                    @endif

                <input type="hidden" id="appointment_time" value="{{$appointment['time']}}">
                <input type="hidden" id="appointment_hd" value="{{$appointment['hairdresser']['name']}}">
                <input type="hidden" id="appointment_service" value="{{$appointment['service']}}">


                <x-form.input
                type="date"
                name="ap_day"
                label="Dia desejado"
                required="true"
                value="{{$appointment['day']}}"
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

               <x-form.submit_btn btnText="Editar agendamento" />
            </form>
        </div>
    </div>
</x-slot:bannerContent>

    <script>
        const apTime = document.getElementById('appointment_time').value;
        const formatedApTime = apTime.substring(0, 5);
        const apTimeSelect = document.getElementById("ap_time");
        for (let i = 0; i < apTimeSelect.options.length; i++) {
            if(apTimeSelect.options[i].textContent == formatedApTime) {
                apTimeSelect.options[i].setAttribute("selected", true);
            }
        }
    </script>
    
    <script>
        const hairdresser = document.getElementById('appointment_hd').value;
        const apHairdresserSelect = document.getElementById("hairdresser_id");
        for (let i = 0; i < apHairdresserSelect.options.length; i++) {
            if(apHairdresserSelect.options[i].textContent == hairdresser) {
                apHairdresserSelect.options[i].setAttribute("selected", true);
            }
        }
    </script>

    <script>
        const service = document.getElementById('appointment_service').value;
        const apServiceSelect = document.getElementById("service_id");
        for (let i = 0; i < apServiceSelect.options.length; i++) {
            if(apServiceSelect.options[i].textContent == service) {
                apServiceSelect.options[i].setAttribute("selected", true);
            }
        }
    </script>

    <script>
        document.getElementById('hairdresser_id').addEventListener('change', function() {
            var hairdresserId = this.value;

            fetch('/hairdresser/' + hairdresserId + '/services/')
                .then(function(response) {
                return response.json();
                })
                .then(function(data) {
                    var serviceSelect = document.getElementById('service_id');

                    serviceSelect.innerHTML = '';

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