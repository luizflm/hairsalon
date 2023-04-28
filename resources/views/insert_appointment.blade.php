<x-layout
page="Hairsalon - Agendamento"
increaseBanner="true"
>
<x-slot:bannerContent>
    <div class="container-fluid form p-0 pt-4 py-sm-5 py-lg-5">
        <div class="container d-flex flex-column align-items-center pb-sm-3 px-sm-3 py-sm-2">
            <x-form.form_title title="Agendamento" />
            <form method="POST" action={{route('set_appointment_action')}} class="mt-2">
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
                    <option value="09:00">09:00</option>
                    <option value="10:00">10:00</option>
                    <option value="11:00">11:00</option>
                    <option value="13:00">13:00</option>
                    <option value="14:00">14:00</option>
                    <option value="15:00">15:00</option>
                    <option value="16:00">16:00</option>
                </x-form.select>
                <x-form.select
                label="Cabelereiro/a"
                name="hairdresser_id"
                required="true"
                >
                    <option value="1">Luiz</option>
                </x-form.select>
                <x-form.select
                label="Serviço"
                name="service_id"
                required="true"
                >
                    <option value="1">Unhas</option>
                </x-form.select>

               <x-form.submit_btn btnText="Criar agendamento" />
            </form>
        </div>
    </div>
</x-slot:bannerContent>

</x-layout>