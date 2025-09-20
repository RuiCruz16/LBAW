@foreach($projects as $project)
    <div class="col">
        @include('partials.project', ['project' => $project])
    </div>
@endforeach