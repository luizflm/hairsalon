<x-admin_layout
page="Hairsalon - Agendamentos Não Concluídos"
increaseBanner="true"
>
    <div class="container-fluid pt-5">
        <div class="container ap-table">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                        <th scope="col">#</th>
                        <th scope="col">Data</th>
                        <th scope="col">Horário</th>
                        <th scope="col">Serviço</th>
                        <th scope="col">Cabelereiro(a)</th>
                        <th scope="col">Usuário</th>
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
                                <td>{{$appointment['ap_date']}}</td>
                                <td>{{$appointment['ap_time']}}</td>
                                <td>{{$appointment['service']}}</td>
                                <td>{{$appointment['hairdresser']}}</td>
                                <td>{{$appointment['user']}}</td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <form method="POST" action={{route('comissions.store',
                                        ['appointment' => [
                                            'id' => $appointment['id'],
                                            'ap_date' => $appointment['ap_date'],
                                            'ap_time' => $appointment['ap_time'],
                                            'hairdresser_id' => $appointment['hairdresser_id'],
                                            'hairdresser_service_id' => $appointment['hairdresser_service_id'],
                                        ]])}}>
                                            @csrf
                                            <button type="submit" class="btn btn-edit me-4" onclick="return confirm('Deseja finalizar o agendamento?');">
                                                Finalizar
                                            </button>
                                        </form>
                                        <a href={{route('appointments.edit', $appointment['id'])}} class="btn btn-edit me-4">Editar</a>
                                        <form method="POST" action={{route('appointments.destroy', $appointment['id'])}}>
                                            @csrf
                                            @method('DELETE')
                                            
                                            <button type="submit" class="btn btn-delete my-2" 
                                            onclick="return confirm('Deseja mesmo deletar o agendamento?');">
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
            <nav aria-label="Page navigation example">
                <ul class="pagination justify-content-center py-2">
                    <li class="page-item">
                        @if($page == 1)
                            <a class="page-link" href={{route('appointments.undone', ['page' => $page])}}>Anterior</a>
                        @else
                            <a class="page-link" href={{route('appointments.undone', ['page' => $page - 1])}}>Anterior</a>
                        @endif
                    </li>
                    @php
                        $pageValue = 1;
                        $pageLoop = 1;
                    @endphp
                    @if($items < 12)
                        @while($pageLoop <= $items)
                            <li class="page-item"><a class="page-link" href={{route('appointments.undone', ['page' => $pageValue])}}>{{$pageValue}}</a></li>
                            @php
                                $pageValue++;
                                $pageLoop = $pageLoop + 4;
                            @endphp
                        @endwhile
                    @else
                        <li class="page-item"><a class="page-link" href={{route('appointments.undone', ['page' => 1])}}>1</a></li>
                        <li class="page-item"><a class="page-link" href={{route('appointments.undone', ['page' => 2])}}>2</a></li>
                        <li class="page-item"><a class="page-link" href={{route('appointments.undone', ['page' => 3])}}>3</a></li>
                    @endif
                    <li class="page-item">
                        <a class="page-link" href={{route('appointments.undone', ['page' => $page + 1])}}>Próxima</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>        
</x-admin_layout>