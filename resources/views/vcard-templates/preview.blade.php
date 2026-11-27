<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $template->name }} preview</title>
</head>
<body style="margin:0">
    <iframe title="{{ $template->name }} preview" srcdoc="{{ $template->html_code }}" sandbox="" style="width:100vw;height:100vh;border:0"></iframe>
</body>
</html>
