<x-admin_layout
page="Hairsalon - Funcionários"
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
                    <th scope="col">Especialidades</th>
                    <th scope="col" class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $row = 0;
                    @endphp
                    @if(!empty($hairdressers))
                        @foreach($hairdressers as $hairdresser)
                            @php
                                $row++;
                            @endphp
                            <tr>
                                <th scope="row">{{$row}}</th>
                                <td>{{$hairdresser['name']}}</td>
                                <td>{{$hairdresser['specialties']}}</td>  
                                <td>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <a href={{route('hairdresser_availability', $hairdresser['id'])}} class="btn btn-edit me-4">Disponibilidade</a>
                                        <a href={{route('edit_hairdresser', $hairdresser['id'])}} class="btn btn-edit me-4">Editar</a>
                                        <form method="POST" action={{route('delete_hairdresser_action', $hairdresser['id'])}}>
                                            @csrf
                                            @method('DELETE')
                                            
                                            <button type="submit" class="btn btn-delete my-2" 
                                            onclick="return confirm('Deseja mesmo deletar o cabelereiro(a)?');">
                                                Deletar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-center py-2">
              <li class="page-item">
                @if($page == 1)
                    <a class="page-link" href={{route('hairdressers', ['page' => $page])}}>Anterior</a>
                @else
                    <a class="page-link" href={{route('hairdressers', ['page' => $page - 1])}}>Anterior</a>
                @endif
              </li>
              @php
                $pageValue = 1;
                $pageLoop = 1;
              @endphp
              @if($items < 12)
                @while($pageLoop <= $items)
                    <li class="page-item"><a class="page-link" href={{route('hairdressers', ['page' => $pageValue])}}>{{$pageValue}}</a></li>
                    @php
                        $pageValue++;
                        $pageLoop = $pageLoop + 4;
                    @endphp
                @endwhile
                @else
                    <li class="page-item"><a class="page-link" href={{route('hairdressers', ['page' => 1])}}>1</a></li>
                    <li class="page-item"><a class="page-link" href={{route('hairdressers', ['page' => 2])}}>2</a></li>
                    <li class="page-item"><a class="page-link" href={{route('hairdressers', ['page' => 3])}}>3</a></li>
                @endif
                <li class="page-item">
                    <a class="page-link" href={{route('hairdressers', ['page' => $page + 1])}}>Próxima</a>
                </li>
            </ul>
        </nav>
    </div>
</div>        
</x-admin_layout>