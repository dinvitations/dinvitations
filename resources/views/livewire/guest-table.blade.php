<div x-data
    x-on:copy-to-clipboard.window="navigator.clipboard.writeText($event.detail.text)">
    {{ $this->table }}
</div>
