@extends('layouts.app')

@section('content')
<div class="container">
    <h2>CSV Import</h2>
    <form id="csv-form" enctype="multipart/form-data">
        <input type="file" name="csv" accept=".csv">
        <button type="submit" class="btn btn-primary">Upload CSV</button>
    </form>

    <hr>

    <h2>Drag & Drop Image Upload</h2>
    <div id="drop-area" style="border:2px dashed #ccc; padding:20px; text-align:center;">
        Drop files here
    </div>
    <div id="progress"></div>
</div>
@endsection

@push('scripts')
<script>
const dropArea = document.getElementById('drop-area');
const progressDiv = document.getElementById('progress');

// Prevent default drag events
['dragenter','dragover','dragleave','drop'].forEach(event =>
    dropArea.addEventListener(event, e => e.preventDefault())
);

dropArea.addEventListener('dragover', () => dropArea.classList.add('dragover'));
dropArea.addEventListener('dragleave', () => dropArea.classList.remove('dragover'));
dropArea.addEventListener('drop', async e => {
    dropArea.classList.remove('dragover');
    let file = e.dataTransfer.files[0];
    let uploadId = Date.now().toString();
    let chunkSize = 1024 * 1024; // 1MB
    let totalChunks = Math.ceil(file.size / chunkSize);

    for (let i = 0; i < totalChunks; i++) {
        let chunk = file.slice(i * chunkSize, (i + 1) * chunkSize);
        let formData = new FormData();
        formData.append('upload_id', uploadId);
        formData.append('chunk_index', i);
        formData.append('total_chunks', totalChunks);
        formData.append('chunk', chunk);

        let res = await fetch('/upload/chunk', { method:'POST', body: formData });
        progressDiv.innerText = `Uploading chunk ${i+1} of ${totalChunks}`;
    }

    // Checksum calculation
    let buffer = await file.arrayBuffer();
    let hash = await crypto.subtle.digest("MD5", buffer);
    let hexChecksum = Array.from(new Uint8Array(hash)).map(b=>b.toString(16).padStart(2,'0')).join('');

    let finalizeData = new FormData();
    finalizeData.append('upload_id', uploadId);
    finalizeData.append('filename', file.name);
    finalizeData.append('checksum', hexChecksum);

    await fetch('/upload/finalize', { method:'POST', body: finalizeData });
    progressDiv.innerText = 'Upload complete!';
});

// CSV AJAX submission
document.getElementById('csv-form').addEventListener('submit', async e => {
    e.preventDefault();
    let formData = new FormData(e.target);
    let res = await fetch('/import', { method:'POST', body: formData });
    let json = await res.json();
    alert(`Imported: ${json.imported}, Updated: ${json.updated}, Invalid: ${json.invalid}, Duplicates: ${json.duplicates}`);
});
</script>
@endpush
