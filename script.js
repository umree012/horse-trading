document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Price range validation
    const minPrice = document.getElementById('min_price');
    const maxPrice = document.getElementById('max_price');
    if (minPrice && maxPrice) {
        document.querySelector('form').addEventListener('submit', function(e) {
            if (minPrice.value && maxPrice.value && parseFloat(minPrice.value) > parseFloat(maxPrice.value)) {
                e.preventDefault();
                alert('Minimum price cannot be greater than maximum price');
            }
        });
    }
});

// Image preview for file uploads
function previewImage(event) {
    const input = event.target;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('imagePreview');
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            } else {
                // Create preview element if it doesn't exist
                const previewDiv = document.createElement('div');
                previewDiv.className = 'mb-3';
                const img = document.createElement('img');
                img.id = 'imagePreview';
                img.src = e.target.result;
                img.className = 'img-thumbnail';
                img.style.maxHeight = '200px';
                previewDiv.appendChild(img);
                input.parentNode.insertBefore(previewDiv, input.nextSibling);
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Add event listeners for image previews
document.querySelectorAll('input[type="file"][accept="image/*"]').forEach(input => {
    input.addEventListener('change', previewImage);
});