<x-layout
page="Hairsalon - Meus Agendamentos"
increaseBanner="true"
>
    <x-slot:bannerContent>
        <div class="container-fluid pt-5">
            <div class="container ap-table">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                            <th scope="col">#</th>
                            <th scope="col">Serviço</th>
                            <th scope="col">Cabelereiro(a)</th>
                            <th scope="col">Data</th>
                            <th scope="col">Horário</th>
                            <th scope="col" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $row = 0;
                            @endphp
                            @foreach($appointments as $appointment)
                            @php
                                $row++;
                            @endphp
                                <tr>
                                    <th scope="row">{{$row}}</th>
                                    <td>{{$appointment['service']}}</td>
                                    <td>{{$appointment['hairdresser']}}</td>
                                    <td>{{$appointment['day']}}</td>
                                    <td>{{$appointment['time']}}</td>  
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <a href={{route('appointments.edit', $appointment['id'])}} class="btn btn-edit me-4">Editar</a>
                                            <form method="POST" action={{route('appointments.destroy', $appointment['id'])}}>
                                                @csrf
                                                @method('DELETE')
                                                
                                                <button type="submit" class="btn btn-delete my-2" 
                                                onclick="return confirm('Deseja mesmo deletar seu agendamento?');">
                                                    Deletar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>        
    </x-slot:bannerContent>
</x-layout>