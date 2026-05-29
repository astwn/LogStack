<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $config['document']['title'] }} - Editor Dokumen</title>
    <style>
        html, body, #placeholder {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
    </style>
</head>
<body>

    <div id="placeholder"></div>

    <script type="text/javascript" src="{{ $onlyofficeUrl }}/web-apps/apps/api/documents/api.js"></script>

    <script type="text/javascript">
        // Ambil data dari backend Laravel
        var config = @json($config);
        
        // Pasangkan token JWT ke dalam config utama agar divalidasi oleh ONLYOFFICE
        config.token = "{{ $token }}";

        // Inisialisasi Editor
        var docEditor = new DocsAPI.DocEditor("placeholder", config);
    </script>
</body>
</html>
