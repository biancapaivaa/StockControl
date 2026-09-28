// Script de confirmação para ações destrutivas
document.querySelectorAll('[data-confirm]').forEach(link => {
    link.addEventListener('click', function(e) {
        if (!confirm(this.getAttribute('data-confirm'))) {
            e.preventDefault();
        }
    });
});

// Função para formatar valores monetários
function formatMoney(value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
}

// Função para formatar datas
function formatDate(dateString) {
    const options = { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString('pt-BR', options);
}

// Adicionar comportamento de focus nos formulários
document.querySelectorAll('input[type="text"], input[type="email"], textarea, select').forEach(field => {
    field.addEventListener('focus', function() {
        this.closest('.form-group')?.classList.add('focused');
    });
    
    field.addEventListener('blur', function() {
        this.closest('.form-group')?.classList.remove('focused');
    });
});

// Auto-ocultar alerts após 5 segundos
document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
        alert.style.opacity = '0';
        alert.style.transition = 'opacity 0.3s';
        setTimeout(() => alert.remove(), 300);
    }, 5000);
});
