// Mobile Menu Toggle
const menuToggle = document.getElementById('menuToggle');
const navLinks = document.getElementById('navLinks');

if (menuToggle && navLinks) {
    menuToggle.addEventListener('click', () => {
        navLinks.classList.toggle('active');
        menuToggle.innerHTML = navLinks.classList.contains('active') 
            ? '<i class="fas fa-times"></i>' 
            : '<i class="fas fa-bars"></i>';
    });
}

// Header Scroll Effect
const header = document.getElementById('header');
window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
});

// Testimonial Slider
const slides = document.querySelectorAll('.testimonial-slide');
const dots = document.querySelectorAll('.slider-dot');
let currentSlide = 0;

function showSlide(index) {
    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));
    
    slides[index].classList.add('active');
    dots[index].classList.add('active');
    currentSlide = index;
}

if (slides.length > 0 && dots.length > 0) { // Ensure slides and dots exist
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => showSlide(index));
    });

    // Auto slide change
    setInterval(() => {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }, 5000);
}

// Intersection Observer for animations
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animated');
        }
    });
}, { threshold: 0.1 });

document.querySelectorAll('.section-title, .collection-card, .brand-card, .testimonial-slide, .cta-content h2, .cta-content p, .cta-button.large').forEach(el => {
    observer.observe(el);
});
// Brand Data - Add your images and brand info here
const brands = [
    {
        name: "Rolex",
        imageUrl: "/assets/images/owl.jpg", // Corrected path
        link: "/auth/login.php"
    },
    {
        name: "Patek Philippe",
        imageUrl: "/assets/images/pp-complicated.jpg", // Corrected path
        link: "/auth/login.php"
    },
    {
        name: "Audemars Piguet",
        imageUrl: "/assets/images/rosegold.jpg", // Corrected path
        link: "/auth/login.php"
    },
    // Add more brands as needed
];

// Function to load brands
function loadBrands() {
    const container = document.getElementById('brandsContainer');
    if (!container) return; // Null check for brandsContainer

    brands.forEach(brand => {
        const brandCard = document.createElement('div');
        brandCard.className = 'brand-card';
        brandCard.innerHTML = `
            <img src="${brand.imageUrl}" alt="${brand.name}" class="brand-logo">
            <div class="brand-overlay">
                <h3>${brand.name}</h3>
                <a href="${brand.link}" class="brand-button">View Watches</a>
            </div>
        `;
        container.appendChild(brandCard);
    });
}

// Call this when the page loads
document.addEventListener('DOMContentLoaded', loadBrands);