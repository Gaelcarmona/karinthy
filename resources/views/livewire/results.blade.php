<div class="text-center">
    {{--    <h2>Résultats de la recherche :</h2>--}}
    <ul style="list-style-type: none;">
        @if ($resultMessages)
            @foreach ($resultMessages as $message)
                @php
                    $links = explode("->",$message);
                    $count = count($links);
                @endphp
                @foreach($links as $key => $link)
                    <a href="{{'https://fr.wikipedia.org/wiki/'.$link }}" target="_blank" style="font-size: larger">{{$link}} @if($key+1 != $count)-> @endif</a>
                @endforeach
                    <br>
            @endforeach
        @endif
    </ul>

    <div wire:loading>
{{--        Chargement en cours...--}}
        <img src="svg-loaders/puff.svg" />
    </div>
</div>
