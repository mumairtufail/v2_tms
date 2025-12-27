<!-- Add this to your layout file (app.blade.php) -->

<style>
.toast {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    padding: 16px 20px;
    border-radius: 8px;
    color: white;
    font-weight: 500;
    min-width: 300px;
    transform: translateX(400px);
    transition: transform 0.3s ease;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.toast.show {
    transform: translateX(0);
}

.toast.success {
    background: #28a745;
}

.toast.error {
    background: #dc3545;
}

.toast .close {
    background: none;
    border: none;
    color: white;
    float: right;
    font-size: 18px;
    font-weight: bold;
    cursor: pointer;
    margin-left: 10px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        showToast("{{ session('success') }}", 'success');
    @endif
    
    @if(session('error'))
        showToast("{{ session('error') }}", 'error');
    @endif
});

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        ${message}
        <button class="close" onclick="this.parentElement.remove()">&times;</button>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 100);
    setTimeout(() => toast.remove(), 4000);
}
</script>