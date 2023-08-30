<div class="ml-[40%]">
    <ul style="list-style-type: none;">
        @if ($paths)
            @foreach (json_decode($paths->data) as $path)
                <li class="w-full">
                    <a href="{{ 'https://fr.wikipedia.org/wiki/' . $start }}" target="_blank"
                        style="font-size: larger;color: #03a696">{{ $start }}</a>
                    <p class="inline mx-2">-></p>
                    @foreach ($path as $key => $link)
                    @php
                    $entry = \App\Models\Entry::find($link);
                    @endphp
                        <a href="{{ 'https://fr.wikipedia.org/wiki/' . $entry->title }}" target="_blank"
                            style="font-size: larger;color: #03a696">{{ $entry->title }}</a>
                        <p class="inline mx-2">-></p>
                    @endforeach
                    <a href="{{ 'https://fr.wikipedia.org/wiki/' . $end }}" target="_blank"
                        style="font-size: larger;color: #03a696">{{ $end }}</a>
                </li>
                <br>
            @endforeach
        @endif
    </ul>
</div>
<script>
    var minutes = 6; // Durée en minutes
    var durationInMilliseconds = minutes * 60 * 1000; // Convertir en millisecondes

    // Planifier l'arrêt de l'action après la durée spécifiée
    var timeoutId = setTimeout(function() {
        clearInterval(intervalId); // Arrêter le rappel périodique
    }, durationInMilliseconds);

    // Rappel périodique
    var intervalId = setInterval(function() {
        @this.call('loadPath');
        console.log("ici");
    }, 1000);
</script>
