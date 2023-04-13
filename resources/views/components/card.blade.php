<div class="col-12 col-lg-4 d-flex justify-content-center mt-5 mt-lg-0">
    <div class="card">
        <img src="/assets/img/{{$cardImg}}" class="card-img-top" alt="Foto da/o Cabelereira/o">
        <div class="card-body">
            <h5 class="card-title">{{$cardTitle}}</h5>
            <p class="card-text">{{$cardDescription}}</p>
        </div>
        <ul class="list-group list-group-flush">
            {{$slot}}
            {{-- <li class="list-group-item">{{$cardDescription}}</li> --}}
        </ul>
    </div>
</div>