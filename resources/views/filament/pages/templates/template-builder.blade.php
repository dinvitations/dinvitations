<x-filament::page>
    <div id="gjs" style="height: 100vh; border: none;"></div>

    {{-- GrapesJS CDN --}}
    <link href="https://unpkg.com/grapesjs/dist/css/grapes.min.css" rel="stylesheet"/>
    <script src="https://unpkg.com/grapesjs"></script>

    {{-- GrapesJS Init --}}
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const editor = grapesjs.init({
                container: '#gjs',
                fromElement: false,
                height: '720px',
                width: 'auto',
                storageManager: { autoload: 0 },
                plugins: ['gjs-preset-webpage'],
                pluginsOpts: {
                    'gjs-preset-webpage': {}
                },
            });

            window.editor = editor;
        });
    </script>

    <x-filament::button onclick="saveContent()">Simpan</x-filament::button>

    <script>
        function saveContent() {
            const html = editor.getHtml();
            const css = editor.getCss();

            fetch('{{ route('templates.save') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ html, css })
            })
            .then(response => response.json())
            .then(data => alert('Disimpan!'));
        }
    </script>

</x-filament::page>
