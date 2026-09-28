document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.target);
            if (!input) return;
            const visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            button.textContent = visible ? 'Mostrar' : 'Ocultar';
        });
    });

    document.querySelectorAll('[data-confirm]').forEach(link => {
        link.addEventListener('click', (event) => {
            if (!confirm(link.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    const input = document.getElementById('imagens');
    const preview = document.getElementById('preview');

    if (input && preview) {
        input.addEventListener('change', () => {
            preview.innerHTML = '';
            [...input.files].forEach(file => {
                if (!file.type.match(/^image\/(jpeg|png|webp)$/)) return;
                const reader = new FileReader();
                reader.onload = e => {
                    const item = document.createElement('div');
                    item.className = 'preview-item';
                    item.innerHTML = `<img src="${e.target.result}" alt="Pré-visualização"><span>${file.name}</span>`;
                    preview.appendChild(item);
                };
                reader.readAsDataURL(file);
            });
        });
    }
});
