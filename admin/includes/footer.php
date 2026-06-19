</main>
<script src="https://cdn.tiny.cloud/1/fs2mgmj61hgaodhdpegjejnhyxk0tbratb8eysiw2k0ap0y0/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
if (typeof tinymce !== 'undefined' && document.querySelector('.rich-editor')) {
    tinymce.init({
        selector: '.rich-editor',
        height: 420,
        menubar: false,
        skin: 'oxide',
        content_css: 'default',
        plugins: 'lists link image code table',
        toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link image table | code',
        content_style: "body { font-family: 'Inter', -apple-system, sans-serif; font-size: 15px; color: #1E1B4B; line-height: 1.7; }",
        body_class: 'velora-editor-body',
        setup: function(editor) {
            editor.on('init', function() {
                editor.getContainer().style.borderRadius = '8px';
                editor.getContainer().style.border = '1.5px solid #E5E1F8';
            });
        }
    });
}
</script>
<script src="<?= SITE_URL ?>/public/js/main.js"></script>
</body>
</html>
