<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        {{$title}}
    </a>
    <ul class="dropdown-menu {{$class ?? ''}}">
        {{$slot}}
    </ul>
</li>