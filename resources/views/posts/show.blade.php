<!doctype html>
<html>
<head>
  <meta charset="UTF-8">
  <title>{{ $post->title }}</title>
</head>
<body>
  <h1>{{ $post->title }}</h1>
  <p>{{ $post->body }}</p>

  <h3>Images:</h3>
  @foreach($post->getMedia('images') as $media)
    <p>
      Original: <img src="{{ $media->getUrl() }}"><br>
      Thumb: <img src="{{ $media->getUrl('thumb') }}"><br>
      Medium: <img src="{{ $media->getUrl('medium') }}"><br>
      Large: <img src="{{ $media->getUrl('large') }}"><br>
    </p>
  @endforeach
</body>
</html>
