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
                            <tr>
                                <th scope="row">1</th>
                                <td>Progressiva com botox</td>
                                <td>Verônica</td>
                                <td>17/04/2023</td>
                                <td>09:00:00</td>   
                                <td>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <a href={{route('update_appointment', ['id' => 1])}} class="btn btn-edit me-4">Editar</a>
                                        <form method="POST" action={{route('delete_appointment', Auth::user()->id)}}>
                                            @csrf
                                            @method('DELETE')
                                            
                                            <button type="submit" class="btn btn-delete my-2" 
                                            onclick="return confirm('Deseja mesmo deletar sua conta?');">
                                                Deletar conta
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">1</th>
                                <td>Progressiva com botox</td>
                                <td>Verônica</td>
                                <td>17/04/2023</td>
                                <td>09:00:00</td>   
                                <td>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <a href={{route('update_appointment', ['id' => 1])}} class="btn btn-edit me-4">Editar</a>
                                        <form method="POST" action={{route('delete_appointment', Auth::user()->id)}}>
                                            @csrf
                                            @method('DELETE')
                                            
                                            <button type="submit" class="btn btn-delete my-2" 
                                            onclick="return confirm('Deseja mesmo deletar sua conta?');">
                                                Deletar conta
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">1</th>
                                <td>Progressiva com botox</td>
                                <td>Verônica</td>
                                <td>17/04/2023</td>
                                <td>09:00:00</td>   
                                <td>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <a href={{route('update_appointment', ['id' => 1])}} class="btn btn-edit me-4">Editar</a>
                                        <form method="POST" action={{route('delete_appointment', Auth::user()->id)}}>
                                            @csrf
                                            @method('DELETE')
                                            
                                            <button type="submit" class="btn btn-delete my-2" 
                                            onclick="return confirm('Deseja mesmo deletar sua conta?');">
                                                Deletar conta
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>        
    </x-slot:bannerContent>



</x-layout>