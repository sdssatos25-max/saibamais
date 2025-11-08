(async () => {
    try {
        await new Promise(r => setTimeout(r, 500));
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = 'red';
        ctx.fillRect(10, 10, 100, 100);
        const fingerprint = canvas.toDataURL();
        if (!fingerprint || fingerprint.length < 100) {
            window.location.href = 'https://paginalimpa.com';
            return;
        }
        await fetch('validar.php');
        location.reload();
    } catch (e) {
        window.location.href = 'https://paginalimpa.com';
    }
})();