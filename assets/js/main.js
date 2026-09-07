/**
 * noontech - Clean Frontend Logic & Interactions
 * Fulfills navigation flow, mobile menus, scroll observations,
 * and high-fidelity prompt generation matching ChatGPT chip style.
 */

document.addEventListener('DOMContentLoaded', () => {
  // Elements
  const navbar = document.querySelector('.navbar');
  const menuToggle = document.querySelector('.menu-toggle');
  const mobileNav = document.getElementById('mobileNav');
  const mobileNavOverlay = document.getElementById('mobileNavOverlay');
  const mobileClose = document.getElementById('mobileClose');
  const quickChips = document.querySelectorAll('.quick-chip');
  const promptTextarea = document.getElementById('projectDetails');
  const contactForm = document.getElementById('contactForm');
  const contactSection = document.getElementById('contact');
  
  // 1. Mobile Navigation Toggle
  if (menuToggle && mobileNav && mobileNavOverlay) {
    const toggleMenu = (open) => {
      mobileNav.classList.toggle('open', open);
      mobileNavOverlay.classList.toggle('open', open);
      document.body.style.overflow = open ? 'hidden' : 'auto';
    };

    menuToggle.addEventListener('click', () => toggleMenu(true));
    if (mobileClose) mobileClose.addEventListener('click', () => toggleMenu(false));
    mobileNavOverlay.addEventListener('click', () => toggleMenu(false));

    // Close menu when clicking links in mobile menu drawer
    const mobileLinks = mobileNav.querySelectorAll('a');
    mobileLinks.forEach(link => {
      link.addEventListener('click', () => toggleMenu(false));
    });
  }

  // 2. Translucent Navigation on Scroll
  window.addEventListener('scroll', () => {
    if (window.scrollY > 20) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
    
    // Highlight Active Page Link based on scroll section
    highlightNavLink();
  });

  // 3. Highlight Navigation Links on scroll (Home, About, Contact)
  const navLinks = document.querySelectorAll('.nav-links a, .mobile-nav-links a');
  const sections = document.querySelectorAll('section[id]');

  function highlightNavLink() {
    let scrollY = window.pageYOffset;
    
    sections.forEach(current => {
      const sectionHeight = current.offsetHeight;
      const sectionTop = current.offsetTop - 100;
      const sectionId = current.getAttribute('id');
      
      if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
        navLinks.forEach(link => {
          link.classList.remove('active');
          if (link.getAttribute('href') === `index.php#${sectionId}` || link.getAttribute('href') === `#${sectionId}`) {
            link.classList.add('active');
          }
        });
      }
    });
  }

  // 4. Quick Action Chips - ChatGPT-style Prompt Injector
  const promptTemplates = {
    'web': `I would like to order a custom website development project. Here is an overview of what I need:
- Website Type: (e.g., Agency website, Portfolio, Landing Page)
- Key Features: (e.g., Responsive layout, Contact Form, MySQL integration)
- Reference Sites: (e.g., openai.com)
- Desired Deadline: `,
    
    'logo': `I want to request a high-quality logo design for our brand noontech. Here is the branding context:
- Brand Name: 
- Brand Message: 
- Style Vibe: (e.g., Minimalist, Geometric, Modern, Bold)
- Preferred Colors: `,
    
    'branding': `I need professional UI/UX Branding for our startup project:
- Target Audience: 
- Core Screens Need: (e.g., Landing, Features table, User dashboard)
- Design Guidelines: (e.g., Dark Theme, Flat layout, Soft shadows)`,
    
    'ecommerce': `I want to build a fully capable, fast E-Commerce website:
- Product Type: (e.g., Digital goods, Apparel)
- Payments Needed: (e.g., PayPal, Stripe, Bank Transfer)
- Key Pages: (e.g., Homepage, Shop, Product Detail, Cart checkout)`,
    
    'dashboard': `I need a clean analytics Dashboard UI for our backend interface:
- Primary Metrics: (e.g., Daily Active Users, Project orders, Live status)
- Interactive Elements: (e.g., Filters, CSV download, Charts)
- Extra Requests: `
  };

  quickChips.forEach(chip => {
    chip.addEventListener('click', () => {
      const type = chip.getAttribute('data-type');
      if (type && promptTemplates[type] && promptTextarea) {
        // Pre-fill text area
        promptTextarea.value = promptTemplates[type];
        
        // Scroll to form cleanly
        if (contactSection) {
          contactSection.scrollIntoView({ behavior: 'smooth' });
        }
        
        // Focus textarea and adjust height
        setTimeout(() => {
          promptTextarea.focus();
          // Set cursor at end of input
          promptTextarea.selectionStart = promptTextarea.selectionEnd = promptTextarea.value.length;
        }, 800);
        
        showToast('Created prompt template for you!', 'success');
      }
    });
  });

  // Expand text area dynamically as user types
  if (promptTextarea) {
    promptTextarea.addEventListener('input', function() {
      this.style.height = 'auto';
      this.style.height = (this.scrollHeight) + 'px';
    });
  }

  // 5. Toast Notifications
  function showToast(message, type = 'success') {
    // Check if toast-container exists
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
      toastContainer = document.createElement('div');
      toastContainer.className = 'toast-container';
      document.body.appendChild(toastContainer);
    }
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerText = message;
    
    toastContainer.appendChild(toast);
    
    // Trigger animations
    setTimeout(() => {
      toast.classList.add('show');
    }, 10);
    
    // Remove toast after 3.5 seconds
    setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => {
        toast.remove();
      }, 300);
    }, 3500);
  }

  // Bind toast global function for backend scripts if needed
  window.showToast = showToast;
});
