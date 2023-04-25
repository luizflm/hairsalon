<x-admin_layout
page="Hairsalon - Agendamentos"
increaseBanner="true"
>
    <div class="container-fluid pt-5">
        <div class="container ap-table">
            <form method="GET" action={{route('comission')}} class="d-flex justify-content-end pt-2">
                <input type="month" name="date" id="input-month" 
                class="p-2" onchange="this.form.submit()" value={{$date}}>
            </form>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Funcionário</th>
                            <th scope="col">Comissão</th>
                            <th scope="col">Serviços Concluídos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $row = 0;
                        @endphp
                        @foreach($list['hairdresser'] as $hairdresser)
                        @php
                            $row++;
                        @endphp
                            <tr>
                                <th scope="row">{{$row}}</th>
                                <td>{{$hairdresser['name']}}</td>
                                <td>{{$hairdresser['comission']}}</td>
                                <td>{{$hairdresser['done_services']}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <nav aria-label="Page navigation example">
                <ul class="pagination justify-content-center py-2">
                    <li class="page-item">
                        @if($items > 4)
                            @if($page == 1)
                                <a class="page-link" href={{route('comission', ['page' => $page])}}>Anterior</a>
                            @else
                                <a class="page-link" href={{route('comission', ['page' => $page - 1])}}>Anterior</a>
                            @endif
                        @endif
                    </li>
                    @php
                        $pageValue = 1;
                        $pageLoop = 1;
                    @endphp
                    @if($items < 12)
                        @while($pageLoop <= $items)
                            <li class="page-item"><a class="page-link" href={{route('comission', ['page' => $pageValue])}}>{{$pageValue}}</a></li>
                            @php
                                $pageValue++;
                                $pageLoop = $pageLoop + 4;
                            @endphp
                        @endwhile
                    @else
                        <li class="page-item"><a class="page-link" href={{route('comission', ['page' => 1])}}>1</a></li>
                        <li class="page-item"><a class="page-link" href={{route('comission', ['page' => 2])}}>2</a></li>
                        <li class="page-item"><a class="page-link" href={{route('comission', ['page' => 3])}}>3</a></li>
                    @endif
                    @if($items > 4)
                        <li class="page-item">
                            <a class="page-link" href={{route('comission', ['page' => $page + 1])}}>Próxima</a>
                        </li>
                    @endif
                </ul>
            </nav>
        </div>
    </div>        
</x-admin_layout>