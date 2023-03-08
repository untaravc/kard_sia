<h5>KRITERIA 3. KEMAHASISWAAN</h5>
<div class="row">
    <div class="col-12">
        <div class="accordion accordion-flush border" id="accordionK3">

            @foreach($k3 as $key => $data)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="flush-headingOne{{$key}}-3">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#flush-collapseOne{{$key}}-3" aria-expanded="false"
                                aria-controls="flush-collapseOne{{$key}}-3">
                            {{$data['code']}} {{$data['title']}}
                        </button>
                    </h2>
                    <div id="flush-collapseOne{{$key}}-3" class="accordion-collapse collapse"
                         aria-labelledby="flush-headingOne{{$key}}-3"
                         data-bs-parent="#accordionK3">
                        <div class="accordion-body">
                            <ul class="list-group">
                                @foreach($data['content'] as $item)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{$item['title']}}
                                        @if($item['link'] != null)
                                            <a href="{{$item['link']}}" target="_blank" class="btn btn-sm btn-success">
                                                Dokumen <i class="fa fa-angle-right"></i>
                                            </a>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach

        </div>

    </div>
</div>
