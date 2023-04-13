<x-layout
page="Hairsalon - Agendamento"
increaseBanner="true"
>
<x-slot:bannerContent>
    <div class="container-fluid form py-3 py-lg-5">
        <div class="container d-flex flex-column align-items-center p-4">
            <div class="form-title">Agendamento</div>
            <form class="mt-2">
                @csrf
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
                    <option value="1">09:00</option>
                    <option value="2">10:00</option>
                    <option value="3">12:00</option>
                    <option value="4">13:00</option>
                    <option value="5">14:00</option>
                    <option value="6">15:00</option>
                    <option value="7">16:00</option>
                </x-form.select>
                <x-form.select
                label="Cabelereiro/a"
                name="hairdresser"
                required="true"
                >
                    <option value="1">Jane Doe</option>
                    <option value="2">John Doe</option>
                    <option value="3">Mary Jane</option>
                </x-form.select>
                <x-form.select
                label="Serviço"
                name="hairdresser_service"
                required="true"
                >
                    <option value="1">Unhas</option>
                    <option value="2">Progresiva</option>
                    <option value="3">Sobrancelha com henna</option>
                </x-form.select>

               <x-form.submit_btn btnText="Criar agendamento" />
            </form>
        </div>
    </div>
</x-slot:bannerContent>

</x-layout>