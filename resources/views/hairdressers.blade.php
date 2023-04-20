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
                        <th scope="col">Nome</th>
                        <th scope="col">Especialidades</th>
                        <th scope="col" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $row = 0;
                        @endphp
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
                    </tbody>
                </table>
            </div>
        </div>
    </div>        
</x-slot:bannerContent>
</x-layout>