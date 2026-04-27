</main>
<script src="https://cdn.tiny.cloud/1/fs2mgmj61hgaodhdpegjejnhyxk0tbratb8eysiw2k0ap0y0/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
if (typeof tinymce !== 'undefined' && document.querySelector('.rich-editor')) {
    tinymce.init({
        selector: '.rich-editor',
        height: 400,
        menubar: false,
        plugins: 'lists link image code table',
        toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link image table | code',
        content_style: "body { font-family: 'Inter', sans-serif; font-size: 15px; }"
    });
}
</script>
<script src="<?= SITE_URL ?>/public/js/main.js"></script>
</body>
</html>
