<h5>KRITERIA 5. KEUANGAN DAN SARANA PRASARANA</h5>
<div class="row">
    <div class="col-12">
        <div class="accordion accordion-flush border" id="accordionK5">

            @foreach($k5 as $key => $data)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="flush-headingOne{{$key}}-5">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#flush-collapseOne{{$key}}-5" aria-expanded="false"
                                aria-controls="flush-collapseOne{{$key}}-5">
                            {{$data['code']}} {{$data['title']}}
                        </button>
                    </h2>
                    <div id="flush-collapseOne{{$key}}-5" class="accordion-collapse collapse"
                         aria-labelledby="flush-headingOne{{$key}}-5"
                         data-bs-parent="#accordionK5">
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
