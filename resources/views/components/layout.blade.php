<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$page ?? 'Hairsalon'}}</title>
    <link rel="icon" type="image/x-icon" href="/assets/img/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <div class="user-area">
                    @auth
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle d-flex justify-content-center align-items-center flex-wrap" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="/assets/img/user_avatar.png" alt="User Avatar">
                                <span>{{Auth::user()->name}}</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href={{route('users.edit', Auth::user()->id)}}>Configurações</a></li>
                                <li><a class="dropdown-item" href={{route('logout')}}>Sair</a></li>
                            </ul>
                        </div>
                    @endauth
                    @guest
                        <a href="{{$btnHref ?? ''}}" class="btn user-area-login">{{$btnText ?? ''}}</a>
                    @endguest
                </div>
                <div class="nav-logo">
                    <a href="#"><img src="/assets/img/logo.png" alt="Logo"></a>
                </div>
                <div class="navbar-toggler-area">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="Toggle navigation">
                        <img src="/assets/img/menu.png" alt="Menu">
                    </button>
                </div>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        @if(request()->route()->named('home'))
                            <x-nav.item-dropdown title="Sobre nós">
                                <li><a class="dropdown-item" href="#contact">Contato</a></li>
                                <li><a class="dropdown-item" href="#hairdressers">Funcionários</a></li>
                            </x-nav.item-dropdown>
                        @endif
                        <x-nav.item-dropdown title="Agendamentos">
                            @auth
                                <li><a class="dropdown-item" href={{route('appointments.index')}}>Meus agendamentos</a></li>
                            @endauth
                            <li><a class="dropdown-item" href={{route('appointments.create')}}>Fazer agendamento</a></li>
                        </x-nav.item-dropdown>
                        <li class="nav-item"><a href="{{route('home')}}" class="nav-link">Home</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <section id="banner" class="{{empty($increaseBanner) ? '' : 'increaseBanner'}}">
            {{$bannerContent}}
        </section>

        {{$slot}}
    </main>

    <footer id="footer">
        <div class="container py-5">
            <div class="row d-flex justify-content-center align-items-center">&copy; Feito por Luiz Felipe</div>
        </div>
    </footer>
    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
    <script src="assets/js/script.js"></script>
</body>

</html>