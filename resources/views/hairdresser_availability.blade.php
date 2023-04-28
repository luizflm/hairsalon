<x-admin_layout
page="Hairsalon - Disponibilidade do Funcionário"
increaseBanner="true"
>
<div class="container-fluid pt-5">
    <div class="container ap-table">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                    <th scope="col">#</th>
                    <th scope="col">Nome</th>
                    <th scope="col">Dia</th>
                    <th scope="col">Horário Inicial</th>
                    <th scope="col">Horário Final</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $row = 0;
                    @endphp
                    @if(!empty($availabilities))
                        @foreach($availabilities as $availability)
                            @php
                                $row++;
                            @endphp
                            <tr>
                                <th scope="row">{{$row}}</th>
                                <td>{{$hairdresser['name']}}</td>
                                <td>{{$availability['weekday']}}</td>  
                                <td>{{$availability['start_time']}}</td>
                                <td>{{$availability['end_time']}}</td>    
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>        
</x-admin_layout>