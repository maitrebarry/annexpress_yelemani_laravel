@props(['file', 'alt'])

<div class="doc-shot-img">
    <img src="{{ asset('images/documentation/'.$file) }}" alt="{{ $alt }}" loading="lazy">
</div>
