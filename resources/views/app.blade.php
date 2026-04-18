<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Repairix') }}</title>
    <link rel="icon" href="{{ asset('images/repairix_short.png') }}" type="image/png" />
    <link rel="apple-touch-icon" href="{{ asset('images/repairix_short.png') }}" />
    @inertiaHead
    @vite(['resources/js/app.js', 'resources/css/app.css'])
  </head>
  <body>
    @inertia

    <script>
      (function(){
        try{
          var s = document.querySelector('script[type="application/json"]');
          var appEl = document.getElementById('app');
          if(s && appEl){
            var txt = (s.textContent || s.innerText || '').trim();
            if(txt) appEl.dataset.page = txt;
          }
        }catch(e){/* no-op */}
      })();
    </script>
  </body>
</html>
