<div class="md:h-172 md:w-172 flex rounded p-3 items-center">
    <ul style="list-style-type: none;">
        @if ($paths)
            @if ($paths->data !== null)
                <div class="md:h-172 md:w-172 flex rounded p-3 items-center">
                    <!-- Ajouter un conteneur SVG pour le graphique -->
                    <svg id="graphCanvas" width="800" height="600"></svg>
                </div>
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
            @else
                <li class="w-full">
                    <a href="{{ 'https://fr.wikipedia.org/wiki/' . $start }}" target="_blank"
                       style="font-size: larger;color: #03a696">{{ $start }}</a>
                    <p class="inline mx-2">-></p>
                    <a href="{{ 'https://fr.wikipedia.org/wiki/' . $end }}" target="_blank"
                       style="font-size: larger;color: #03a696">{{ $end }}</a>
                </li>
            @endif
        @endif
    </ul>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchButton = document.querySelector('.search-button');
        let intervalId;

        function startLoadPathInterval() {
            var minutes = 6;
            var durationInMilliseconds = minutes * 60 * 1000;
            var timeoutId = setTimeout(function () {
                clearInterval(intervalId);
            }, durationInMilliseconds);

            intervalId = setInterval(function () {
            @this.call('loadPath')
                ;
            }, 1000);
        }

        document.addEventListener('stopScript', function () {
            setTimeout(function () {
                clearInterval(intervalId);
            }, 5000);
        });


        searchButton.addEventListener('click', function () {
            startLoadPathInterval();
        });
        document.addEventListener('updatePathsData', function (pathsData) {
            // console.log(pathsData.detail.pathsData)
            const updatedPathsData = JSON.parse(pathsData.detail.pathsData);
            // console.log(updatedPathsData.data);
            const tableauInitial = JSON.parse(updatedPathsData.data);
                // console.log(tableauInitial)

            const startEntryId = updatedPathsData.start_entry_id;
            const endEntryId = updatedPathsData.end_entry_id;
            const data = {
                name: String(startEntryId),
                children: tableauInitial.map(item => {
                    return {
                        name: String(item[0]),
                        children: [
                            {
                                name: String(endEntryId),
                                value: endEntryId
                            }
                        ]
                    };
                })
            };
            console.log(data);
            console.log(JSON.stringify(data, null, 2));


        });
    });
</script>
