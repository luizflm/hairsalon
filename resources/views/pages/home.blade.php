<x-layout 
page="Hairsalon - Home"
btnHref="{{route('login')}}"
btnText="Fazer Login"
>
    <x-slot:bannerContent>
        <div class="container-fluid pt-5 p-lg-5">
            <div class="container">
                <div class="row">
                    <div class="banner-text col-12 mt-5">Renovamos <span>vidas</span>,</div>
                </div>
                <div class="row">
                    <div class="banner-text col-12">restauramos <span>autoestima.</span></div>
                </div>
                <div class="row">
                    <div class="banner-text col-12">Venha nos conhecer!</div>
                </div>
            </div>
        </div>
    </x-slot:bannerContent>

    <section id="hairdressers">
        <div class="container py-5">
            <div class="section-title">Funcionários:</div>
            <div class="row d-flex justify-content-center mt-0 mt-lg-5">
                @foreach($hairdressers as $hairdresser)
                    <x-card cardTitle="{{$hairdresser['name']}}" cardImg="{{$hairdresser['avatar']}}" >
                        @foreach($hairdresser['specialties'] as $hdSpecialty)
                        <li class="list-group-item">{{$hdSpecialty}}</li>
                        @endforeach
                    </x-card>
                @endforeach
            </div>
        </div>
    </section>

    <section id="contact">
        <div class="container py-5">
            <div class="section-title">Contato:</div>
            <div class="row">
                <div class="contact-left col-12 col-lg-6 mt-5">
                    <div class="contact-info p-3 p-lg-5">
                        <x-contact-info
                        imgName="localizacao.png"
                        imgAlt="Localização"
                        text="Avenida das Américas, 19019"
                        />
                        <x-contact-info
                        imgName="telefone.png"
                        imgAlt="Telefone"
                        text="+55 21 99999-9999"
                        class="mt-4"
                        />
                        <x-contact-info
                        imgName="carta.png"
                        imgAlt="Email"
                        text="teste@noreply.com"
                        class="mt-4"
                        />
                    </div>
                </div>
                <div class="contact-right col-12 col-lg-6 mt-3 mt-lg-0"><iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3672.047947962992!2d-43.49055508539755!3d-23.022011647779657!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9bdce92a2a6cc5%3A0xadaf4f34cb84ec4d!2sRecreio%20Shopping!5e0!3m2!1spt-BR!2sbr!4v1680732428362!5m2!1spt-BR!2sbr" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>
            </div>
        </div>
    </section>
</x-layout>