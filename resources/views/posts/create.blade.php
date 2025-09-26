<!doctype html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Create Post</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css"/>
</head>
<body>
  <h1>Create Post</h1>

  <form id="post-form" action="{{ route('posts.store') }}" method="POST">
    @csrf
    <input type="text" name="title" placeholder="Title"><br>
    <textarea name="body" placeholder="Body"></textarea><br>

    <!-- Dropzone element -->
    <div id="my-dropzone" class="dropzone"></div>

    <button type="submit">Save</button>
  </form>

  <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
  <script>
    Dropzone.autoDiscover = false;

    const dz = new Dropzone("#my-dropzone", {
      url: "#", // we’ll send files with form instead
      autoProcessQueue: false,
      uploadMultiple: true,
      parallelUploads: 10,
      maxFilesize: 10, // MB
      acceptedFiles: "image/*",
      addRemoveLinks: true
    });

    document.querySelector('#post-form').addEventListener('submit', function(e){
      e.preventDefault();
      const form = e.target;
      const formData = new FormData(form);

      dz.getAcceptedFiles().forEach(file => {
        formData.append('images[]', file);
      });

      fetch(form.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
      })
      .then(r => {
        if (!r.ok) throw new Error("Upload failed");
        return r.json();
      })
      .then(data => {
        alert("Post created! Images queued for conversion.");
        window.location.href = data.redirect;
      })
      .catch(err => {
        console.error(err);
        alert("Something went wrong");
      });
    });
  </script>
</body>
</html>
