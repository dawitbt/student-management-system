document.addEventListener('DOMContentLoaded', function() {
  // Toggle password visibility
  document.querySelectorAll('.toggle-password').forEach(function(icon) {
    icon.addEventListener('click', function() {
      const input = document.querySelector(this.getAttribute('toggle'));
      const icon = this.querySelector('i');
      
      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      }
    });
  });

  // Confirm before delete
  document.querySelectorAll('.btn-delete').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      if (!confirm('Are you sure you want to delete this student?')) {
        e.preventDefault();
      }
    });
  });

  // Toast notifications
  const toast = document.querySelector('.toast');
  if (toast) {
    setTimeout(() => {
      toast.style.opacity = '0';
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  // Form validation
  document.querySelectorAll('form').forEach(function(form) {
    form.addEventListener('submit', function(e) {
      let valid = true;
      
      // Check required fields
      form.querySelectorAll('[required]').forEach(function(input) {
        if (!input.value.trim()) {
          input.style.boxShadow = '0 0 0 2px var(--danger)';
          valid = false;
          
          // Remove error style when user starts typing
          input.addEventListener('input', function() {
            this.style.boxShadow = '';
          });
        }
      });
      
      if (!valid) {
        e.preventDefault();
        const alert = document.createElement('div');
        alert.className = 'alert alert-error';
        alert.innerHTML = '<i class="fas fa-exclamation-circle"></i> Please fill all required fields';
        form.prepend(alert);
        
        // Scroll to alert
        alert.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    });
  });

  // Floating action button animation
  const fab = document.querySelector('.fab');
  if (fab) {
    fab.addEventListener('mouseenter', function() {
      this.style.transform = 'translateY(-5px) scale(1.05)';
    });
    
    fab.addEventListener('mouseleave', function() {
      this.style.transform = '';
    });
  }
});